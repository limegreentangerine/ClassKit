<?php

namespace ClassKit\Api;

use ClassKit\Api\Enum\ResponseType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as CoreResponse;

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
     * @return CoreResponse
     */
    public static function fromType(ResponseType $type, mixed $data = [], int $status = 200, array $headers = []): CoreResponse
    {
        return match ($type) {
            ResponseType::JSON => new JsonResponse($data, $status, $headers),
            ResponseType::XML => self::createXmlResponse($data, $status, $headers),
        };
    }
}
