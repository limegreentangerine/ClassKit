<?php

namespace ClassKit\Search;

use Pagerfanta\View\TwitterBootstrap5View;
use Concrete\Core\Search\Result\Result as SearchResult;

class Result extends SearchResult
{
    /**
     * @return string
     */
    public function getPaginationHTML(): string
    {
        if ($this->pagination->haveToPaginate()) {
            $view = new TwitterBootstrap5View();
            $me = $this;
            $result = $view->render(
                $this->pagination,
                function ($page) use ($me) {
                    $list = $me->getItemListObject();
                    $result = (string) $me->getBaseURL();
                    $result .= strpos($result, '?') === false ? '?' : '&';
                    $result .= ltrim($list->getQueryPaginationPageParameter(), '&') . '=' . $page;

                    return $result;
                },
                [
                    'prev_message' => tc('Pagination', '&larr; Previous'),
                    'next_message' => tc('Pagination', 'Next &rarr;'),
                    'active_suffix' => '<span class="sr-only">' . tc('Pagination', '(current)') . '</span>',
                ],
            );
        } else {
            $result = '';
        }

        return $result;
    }
}
