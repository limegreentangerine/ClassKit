<?php

namespace ClassKit\Page\AjaxPage;

final readonly class AjaxPageResponse
{
    public function __construct(
        /**
         * @var array<int, Page>
         */
        public array $pages,
        public string $html,
        public ?int $nextPageNum,
        public ?bool $hasNextPage,
    ) {}

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
