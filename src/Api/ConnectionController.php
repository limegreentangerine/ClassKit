<?php

namespace ClassKit\Api;

use SimpleXMLElement;
use GuzzleHttp\TransferStats;
use InvalidArgumentException;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Api\Enum\RequestMethod;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use ClassKit\Api\Interface\ConnectionInterface;

abstract class ConnectionController implements ConnectionInterface
{
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
     * @var string       $baseUrl
     * @var ResponseType $format
     * @var ?array       $headers
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $baseUrl, string $format, ?array $headers)
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
     * Build Fule Request URL from Path
     *
     * @var    string $path
     * @return string
     */
    protected function buildRequestUrl(string $path): string
    {
        return sprintf('%s%s', rtrim($this->getBaseUrl(), '/'), str_replace($this->getBaseUrl(), '', $path));
    }

    protected function constructRequestBody(array $data)
    {
        $body = null;

        switch ($this->format) {
            case 'xml':
                $xml = new SimpleXMLElement('<root/>');
                $this->arrayToXml($data, $xml);
                $body = $xml->asXML() ?? null;
                break;
            case 'json':
            default:
                $body = json_encode($data) ?? null;
                break;
        }

        return $body;
    }

    protected function arrayToXml(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
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
            case 'json':
                $responseHeaders['Content-Type'] = 'application/json';
                break;
            case 'xml':
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
    public function makeRequest(string $method, string $path, ?array $data, ?array $headers): Response
    {
        $url = $this->buildRequestUrl($path);

        if (!RequestMethod::tryFrom(strtoupper($method))) {
            return ErrorResponse::fromType(
                $this->format,
                $this->constructRequestBody([
                    'message' => t('Invalid request method: %s', $method)
                ]),
                Response::HTTP_BAD_REQUEST,
                $headers
            );
        }

        try {
            $options = [
                'debug' => false,
                'headers' => $this->getHeaders(),
                'idn_conversion' => false,
                'connect_timeout' => 5,
                'timeout' => 10,
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                },
            ];

            if ($data) {
                $options['body'] = $this->constructRequestBody($data);
            }

            $res = $this->client->request($method, $url, $options);

            return Response::fromType(
                $this->format,
                $res->getBody(),
                $res->getStatusCode(),
                $res->getHeaders()
            );
        } catch (RequestException $e) {
            return ErrorResponse::fromType(
                $this->format,
                $e->getResponse()->getBody(),
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getHeaders()
            );
        }
    }
}
