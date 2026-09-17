<?php

namespace LgtToolkit\Page\AjaxPage;

use LgtToolkit\Page\AjaxPage\Enums\SortOrder;

/**
 * Represents the request payload for a paginated AJAX page.
 */
final readonly class AjaxPageRequest
{
    /**
     * Create a new AJAX page request object.
     *
     * @param int            $pageNum   The page number requested by the client.
     * @param int|null       $perPage   The number of items per page.
     * @param SortOrder|null $sortOrder The requested sort order.
     */
    public function __construct(
        public int $pageNum,
        public ?int $perPage,
        public ?SortOrder $sortOrder,
    ) {}

    /**
     * Creates a request object from an associative array.
     *
     * @param array $data The request payload from the client.
     *
     * @return self A hydrated request object.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pageNum: $data['pageNum'],
            perPage: $data['perPage'] ?? null,
            sortOrder: isset($data['sortOrder'])
                ? SortOrder::from($data['sortOrder'])
                : null,
        );
    }

    /**
     * Converts the request into an array for serialization.
     *
     * @return array<string, mixed> The serialized request payload.
     */
    public function toArray(): array
    {
        return [
            'pageNum' => $this->pageNum,
            'perPage' => $this->perPage ?? null,
            'sortOrder' => $this->sortOrder ?? null,
        ];
    }
}
