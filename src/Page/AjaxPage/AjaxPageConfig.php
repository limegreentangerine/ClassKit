<?php

namespace ClassKit\Page\AjaxPage;

use ClassKit\Page\PageList;
use Concrete\Core\Page\Page;
use Concrete\Core\Entity\Package;
use ClassKit\Page\AjaxPage\Enums\SortOrder;

/**
 * Holds the configuration options for a paginated AJAX page.
 */
final readonly class AjaxPageConfig
{
    /**
     * Create a new AJAX page configuration object.
     *
     * @param int          $startPage         The first page number to load.
     * @param int          $perPage           The number of records to include per page.
     * @param SortOrder    $sortOrder         The sort order to apply.
     * @param ?string      $cardPath          The card template path.
     * @param ?PageList    $pl                The page list to paginate.
     * @param ?Page        $parent            The parent page to restrict results to.
     * @param ?bool        $debug             Whether debugging is enabled.
     * @param ?bool        $includeExclusions Whether excluded pages should be included.
     * @param ?string      $noResultsMessage  The empty-state message.
     * @param Package|null $pkg               The package providing the page card templates.
     */
    public function __construct(
        public int $startPage,
        public int $perPage,
        public SortOrder $sortOrder,
        public ?string $cardPath,
        public ?PageList $pl,
        public ?Page $parent,
        public ?bool $debug,
        public ?bool $includeExclusions,
        public ?string $noResultsMessage,
        public ?Package $pkg,
    ) {}

    /**
     * Converts the configuration to an array for JSON serialization.
     *
     * @return array<string, mixed> The array representation of the configuration.
     */
    public function toArray(): array
    {
        return [
            'startPage' => $this->startPage,
            'perPage' => $this->perPage,
            'sortOrder' => $this->sortOrder,
            'cardPath' => $this->cardPath,
            'pageList' => $this->pl,
            'parentPage' => $this->parent,
            'debug' => $this->debug,
            'includeExclusions' => $this->includeExclusions,
            'noResultsMessage' => $this->noResultsMessage,
            'pkg' => ($this->pkg) ? $this->pkg->getPackageHandle() : null,
        ];
    }
}
