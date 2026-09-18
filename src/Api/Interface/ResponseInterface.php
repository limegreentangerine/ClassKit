<?php

namespace ClassKit\Api\Interface;

/**
 * Interface ResponseInterface.
 */
interface ResponseInterface
{
    /**
     * Executes getStatusText.
     */
    public function getStatusText(string $code): string;
}
