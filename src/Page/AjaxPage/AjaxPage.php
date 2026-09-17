<?php

namespace LgtToolkit\Page\AjaxPage;

use Page;
use View;
use Package;
use PageList;
use LgtToolkit\Page\TranslationAdaptorTrait;
use LgtToolkit\Page\AjaxPage\Enums\SortOrder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Localization\Translator\Adapter\Laminas\TranslatorAdapter;

/**
 * Base class for paginated AJAX page responses.
 */
abstract class AjaxPage
{
    use TranslationAdaptorTrait;

    protected AjaxPageResponse $response;
    protected PageList $pl;
    protected ?Page $parent;
    protected int $currentPage;
    protected int $perPage;
    protected SortOrder $sortOrder;
    protected bool $includeExclusions;
    protected string $noResultsMessage;
    protected bool $debug;
    protected string $cardPath;
    protected ?Package $pkg;
    protected ?TranslatorAdapter $ta;

    /**
     * Create a paginated AJAX page instance.
     *
     * @param AjaxPageConfig $options The configuration supplied to the page builder.
     */
    public function __construct(AjaxPageConfig $options)
    {
        $this->pl = $options->pl ?? new PageList();
        $this->parent = $options->parent;
        $this->currentPage = $options->startPage;
        $this->perPage = $options->perPage;
        $this->sortOrder = $options->sortOrder;
        $this->cardPath = $options->cardPath ?? '/cards/article';
        $this->includeExclusions = $options->includeExclusions ?? false;
        $this->debug = $options->debug ?? false;
        $this->noResultsMessage = t($options->noResultsMessage) ?? t('No articles found');

        $this->ta = $this->getTranslationAdaptor($options->parent ?? Page::getCurrentPage());

        $this->build();
    }

    /**
     * Build the page list configuration used by the AJAX response.
     */
    protected function build(): void
    {
        if ($this->debug === true) {
            $this->getPageList()->debug();
        }

        if ($this->parent && $this->parent instanceof Page) {
            $this->getPageList()->filterByParentID($this->parent->getCollectionID());
        }

        $this->getPageList()->filter('cvName', '', '!=');
        $this->getPageList()->filterByExcludePageList($this->includeExclusions);
        $this->getPageList()->setItemsPerPage($this->perPage);
        $this->setSortOrder();
    }

    /**
     * Applies the configured sort order to the underlying page list.
     */
    protected function setSortOrder()
    {
        switch ($this->sortOrder) {
            case 'sitemap_asc':
                $this->getPageList()->sortByDisplayOrder();
                break;
            case 'sitemap_desc':
                $this->getPageList()->sortByDisplayOrderDescending();
                break;
            case 'date_asc':
                $this->getPageList()->sortByPublicDate();
                break;
            case 'modified_date_asc':
                $this->getPageList()->sortByDateModified();
                break;
            case 'modified_date_desc':
                $this->getPageList()->sortByDateModifiedDescending();
                break;
            case 'random':
                $this->getPageList()->sortBy('RAND()');
                break;
            case 'name_asc':
                $this->getPageList()->sortByName();
                break;
            case 'name_desc':
                $this->getPageList()->sortByNameDescending();
                break;
            case 'date_desc':
            default:
                $this->getPageList()->sortByPublicDateDescending();
                break;
        }
    }

    /**
     * Renders the HTML for an array of page records.
     *
     * @param array $pages The page records to render.
     *
     * @return string The rendered page-card markup.
     */
    protected function buildView(array $pages): string
    {
        $view = new View();
        ob_start();
        foreach ($pages as $page) {
            $view->element($this->cardPath, [
                'page' => $page,
                'ta' => $this->ta,
            ], ($this->pkg) ? $this->pkg->getPackageHandle() : 'lgt_toolkit');
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Gets the underlying page list used by the AJAX page.
     *
     * @return PageList The configured page list.
     */
    public function getPageList(): PageList
    {
        return $this->pl;
    }

    /**
     * Returns the next paginated batch of results as a JSON response.
     *
     * @return Response The AJAX response payload.
     */
    public function getNextPage(): Response
    {
        $request = AjaxPageRequest::fromArray($_GET);

        $this->currentPage = (isset($request->pageNum)) ? $request->pageNum : $this->currentPage;

        if ($request->perPage) {
            $this->perPage = $request->perPage;
            $this->getPageList()->setItemsPerPage($this->perPage);
        }

        if ($request->sortOrder) {
            $this->sortOrder = $request->sortOrder;
            $this->setSortOrder();
        }

        $pagination = $this->getPageList()->getPagination();
        $pagination->setCurrentPage($this->currentPage);
        $pages = $pagination->getCurrentPageResults();

        $html = $this->buildView($pages);

        $this->response = new AjaxPageResponse(
            pages: $pages,
            html: $html,
            nextPageNum: ($pagination->hasNextPage()) ? ($this->currentPage + 1) : 0,
            hasNextPage: $pagination->hasNextPage(),
        );

        return new JsonResponse($this->response->toArray(), 200);
    }
}
