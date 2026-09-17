<?php

namespace ClassKit\Api\Response;

use ClassKit\Api\Enum\ResponseType;

class ErrorResponse extends Response
{
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
}
