<?php

namespace ClassKit\Api;

interface ResponseInterface
{
    public function getStatusText(string $code): string;
}
