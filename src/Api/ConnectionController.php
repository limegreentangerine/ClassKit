<?php

namespace ClassKit\Api;

use SimpleXMLElement;
use GuzzleHttp\TransferStats;
use InvalidArgumentException;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Api\Response\Response;
use ClassKit\Api\Enum\RequestMethod;
use GuzzleHttp\Client as HttpClient;
use ClassKit\Api\Response\ErrorResponse;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use ClassKit\Api\Interface\ConnectionInterface;

/**
 * Class ConnectionController.
 */
abstract class ConnectionController implements ConnectionInterface
{
    /**
     * cURL error number for an operation timeout (CURLE_OPERATION_TIMEDOUT).
     */
    private const CURL_TIMEOUT_ERRNO = 28;

    /**
     * @var HttpClient
     */
    protected HttpClient $client;

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var ResponseType
     */
    protected ResponseType $format;

    /**
     * Seconds to wait for a response. Subclasses can override for slow endpoints.
     *
     * @var int
     */
    protected int $timeout = 10;

    /**
     * Seconds to wait while connecting. Subclasses can override.
     *
     * @var int
     */
    protected int $connectTimeout = 5;

    /**
     * @var string       $baseUrl
     * @var ResponseType $format
     * @var ?array       $headers
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $baseUrl, string $format, ?array $headers = [])
    {
        $this->client = new HttpClient();

        if (!ResponseType::tryFrom($format)) {
            throw new InvalidArgumentException(t('Invalid response format: %s', $format));
        }
        $this->format = ResponseType::from($format);

        $this->setBaseUrl($baseUrl);
        $this->setHeaders($headers ?? []);

        $this->setResponseHeaders();
    }

    /**
     * Build a request URL from a path.
     */
    protected function buildRequestUrl(string $path): string
    {
        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Executes constructRequestBody.
     */
    protected function constructRequestBody(array $data): ?string
    {
        $body = null;

        switch ($this->format) {
            case ResponseType::XML:
                $xml = new SimpleXMLElement('<root/>');
                $this->arrayToXml($data, $xml);
                $body = $xml->asXML() ?: null;
                break;
            case ResponseType::JSON:
            default:
                $body = json_encode($data) ?: null;
                break;
        }

        return $body;
    }

    /**
     * Executes arrayToXml.
     */
    protected function arrayToXml(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild((string) $key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild((string) $key, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Build an error body for the connection's format.
     *
     * XML responses need a string body; JSON responses take an array.
     */
    protected function buildErrorBody(string $message): array|string
    {
        if ($this->format === ResponseType::XML) {
            return '<error><message>' . htmlspecialchars($message, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</message></error>';
        }

        return ['message' => $message];
    }

    /**
     * Get the value of baseUrl
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Set the value of baseUrl
     *
     * @param string $baseUrl
     *
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get the value of headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the value of headers
     *
     * @param array $headers
     *
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Sets response headers base on constructed type
     */
    public function setResponseHeaders(): void
    {
        $responseHeaders = [];

        switch ($this->format) {
            case ResponseType::JSON:
                $responseHeaders['Content-Type'] = 'application/json';
                break;
            case ResponseType::XML:
                $responseHeaders['Content-Type'] = 'text/xml';
                break;
            default:
                break;
        }

        $this->setHeaders($responseHeaders);
    }

    /**
     * Make API Request
     *
     * @var string $method
     * @var string $path
     * @var ?array $data
     * @var ?array $headers
     *
     * @return Response
     */
    public function makeRequest(string $method, string $path, ?array $data = null, ?array $headers = []): Response
    {
        $url = $this->buildRequestUrl($path);
        $requestHeaders = array_merge($this->getHeaders(), $headers ?? []);

        if (!RequestMethod::tryFrom(strtoupper($method))) {
            return ErrorResponse::fromType(
                $this->format,
                $this->buildErrorBody(t('Invalid request method: %s', $method)),
                Response::HTTP_BAD_REQUEST,
                $requestHeaders,
            )->setUrl($url);
        }

        try {
            $options = [
                'debug' => false,
                'headers' => $requestHeaders,
                'idn_conversion' => false,
                'connect_timeout' => $this->connectTimeout,
                'timeout' => $this->timeout,
                // Follows redirects, so the response reports the URL that answered.
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = (string) $stats->getEffectiveUri();
                },
            ];

            if ($data !== null) {
                $options['body'] = $this->constructRequestBody($data);
            }

            $res = $this->client->request($method, $url, $options);

            $responseBody = (string) $res->getBody();
            $responseHeaders = $res->getHeaders();

            return Response::fromType(
                $this->format,
                $this->format === ResponseType::JSON ? json_decode($responseBody, true) ?? $responseBody : $responseBody,
                $res->getStatusCode(),
                $responseHeaders,
            )->setUrl($url);
        } catch (GuzzleException $e) {
            $response = $e instanceof RequestException ? $e->getResponse() : null;
            $errorHeaders = $response ? $response->getHeaders() : [];

            if ($response) {
                $statusCode = $response->getStatusCode();
                $errorBody = (string) $response->getBody();
            } else {
                $errno = method_exists($e, 'getHandlerContext') ? ($e->getHandlerContext()['errno'] ?? null) : null;
                $statusCode = $errno === self::CURL_TIMEOUT_ERRNO
                    ? Response::HTTP_GATEWAY_TIMEOUT
                    : Response::HTTP_INTERNAL_SERVER_ERROR;
                $errorBody = $this->buildErrorBody(t('Request failed: %s', $e->getMessage()));
            }

            if ($this->format === ResponseType::JSON && is_string($errorBody)) {
                $decodedBody = json_decode($errorBody, true);
                $errorBody = $decodedBody ?? ['message' => $errorBody];
            }

            return ErrorResponse::fromType($this->format, $errorBody, $statusCode, $errorHeaders)->setUrl($url);
        }
    }
}
