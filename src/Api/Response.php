<?php

namespace ClassKit\Api;

use InvalidArgumentException;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Api\Interface\ResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as CoreResponse;

class Response extends CoreResponse implements ResponseInterface
{
    /**
     * @var string
     */
    protected string $url;

    /**
     * @var string
     */
    protected string $body;

    /**
     * @param mixed $data
     */
    protected static function createXmlResponse(mixed $data, int $status, array $headers): self
    {
        if (null !== $data && !is_string($data)) {
            throw new InvalidArgumentException('XML response data must be a string or null.');
        }

        return new self($data ?? '', $status, $headers);
    }
    /**
     * Create Response from chosen type
     *
     * @param ResponseType $type
     * @param mixed        $data
     * @param int          $status
     * @param array        $headers
     *
     * @return CoreResponse
     */
    public static function fromType(ResponseType $type, mixed $data = [], int $status = 200, array $headers = []): CoreResponse
    {
        return match ($type) {
            ResponseType::JSON => new JsonResponse($data, $status, $headers),
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
        return Response::$statusTexts[$code];
    }
}
