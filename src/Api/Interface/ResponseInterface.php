<?php

namespace ClassKit\Api\Interface;

interface ResponseInterface
{
    public function getStatusText(string $code): string;
}
