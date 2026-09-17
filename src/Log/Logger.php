<?php

namespace ClassKit\Log;

use Core;
use Monolog\Logger as MonologLogger;
use Concrete\Core\Logging\LoggerFactory;

class Logger extends MonologLogger
{
    protected MonologLogger $logger;
    protected string $channel;
    protected array $context = [];

    public function __construct(string $name)
    {
        $this->channel = $name;
        $this->logger = Core::make(LoggerFactory::class)->createLogger($this->channel);
    }

    /**
     * Get the value of logger
     */
    public function getLogger(): MonologLogger
    {
        return $this->logger;
    }

    /**
     * Set the value of context
     *
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }
}
