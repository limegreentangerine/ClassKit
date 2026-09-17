<?php

namespace ClassKit\Api\Response;

use InvalidArgumentException;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Api\Interface\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as CoreResponse;

class Response extends CoreResponse implements ResponseInterface
{
    /**
     * @var string
     */
    protected string $url = '';

    /**
     * @var string
     */
    protected string $body = '';

    public function __construct(mixed $content = '', int $status = 200, array $headers = [])
    {
        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_THROW_ON_ERROR);
            $headers['Content-Type'] ??= 'application/json';
        }

        if (null !== $content && !is_string($content)) {
            $content = (string) $content;
        }

        parent::__construct($content ?? '', $status, $headers);

        $this->body = $this->getContent() ?? '';
    }

    /**
     * @param mixed $data
     */
    protected static function createXmlResponse(mixed $data, int $status, array $headers): self
    {
        if (null !== $data && !is_string($data)) {
            throw new InvalidArgumentException('XML response data must be a string or null.');
        }

        $responseHeaders = $headers;
        $responseHeaders['Content-Type'] ??= 'text/xml';

        return new self($data ?? '', $status, $responseHeaders);
    }

    /**
     * Create Response from chosen type
     *
     * @param ResponseType $type
     * @param mixed        $data
     * @param int          $status
     * @param array        $headers
     *
     * @return self
     */
    public static function fromType(ResponseType $type, mixed $data = [], int $status = 200, array $headers = []): self
    {
        return match ($type) {
            ResponseType::JSON => new self($data, $status, $headers),
            ResponseType::XML => self::createXmlResponse($data, $status, $headers),
        };
    }

    /**
     * Get the value of url
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of statusCode
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the value of body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        $this->setContent($body);

        return $this;
    }

    public function getStatusText(string $code): string
    {
        return self::$statusTexts[$code] ?? 'Unknown status';
    }
}
