<?php

namespace ClassKit\Api\Interface;

use ClassKit\Api\Response\Response;

interface ConnectionInterface
{
    public function makeRequest(string $method, string $path, ?array $data = null, ?array $headers = []): Response;
}
