<?php

namespace ClassKit\Api;

use Symfony\Component\HttpFoundation\Response as CoreResponse;

class Response extends CoreResponse implements ResponseInterface
{
    protected string $url;

    protected int $statusCode;

    protected string $body;

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
     * Sets the response status code.
     *
     * If the status text is null it will be automatically populated for the known
     * status codes and left empty otherwise.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @final
     */
    public function setStatusCode(int $code, ?string $text = null): object
    {
        $this->statusCode = $code;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        if (null === $text) {
            $this->statusText = self::$statusTexts[$code] ?? 'unknown status';

            return $this;
        }

        if (false === $text) {
            $this->statusText = '';

            return $this;
        }

        $this->statusText = $text;

        return $this;
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

        return $this;
    }

    public function getStatusText(string $code): string
    {
        return Response::$statusTexts[$code];
    }
}
