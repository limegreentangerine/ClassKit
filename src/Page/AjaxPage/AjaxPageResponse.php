<?php

namespace ClassKit\Page\AjaxPage;

final readonly class AjaxPageResponse
{
    /**
     * Executes __construct.
     */
    public function __construct(
        /**
         * @var array<int, Page>
         */
        public array $pages,
        public string $html,
        public ?int $nextPageNum,
        public ?bool $hasNextPage,
    ) {}

    /**
     * Executes toArray.
     */
    public function toArray(): array
    {
        return [
            'pages' => $this->pages,
            'html' => $this->html,
            'nextPageNum' => $this->nextPageNum,
            'hasNextPage' => $this->hasNextPage,
        ];
    }
}
