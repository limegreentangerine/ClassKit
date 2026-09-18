<?php

namespace ClassKit\Api\Interface;

use ClassKit\Api\Response\Response;

/**
 * Interface ConnectionInterface.
 */
interface ConnectionInterface
{
    /**
     * Executes makeRequest.
     */
    public function makeRequest(string $method, string $path, ?array $data = null, ?array $headers = []): Response;
}
