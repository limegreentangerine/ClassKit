<?php

namespace LgtToolkit\Page\AjaxPage\Enums;

/**
 * Defines the available sort orders for AJAX page results.
 */
enum SortOrder: string
{
    case SitemapAsc = 'sitemap_asc';
    case SitemapDesc = 'sitemap_desc';
    case DateAsc = 'date_asc';
    case DateDesc = 'date_desc';
    case ModifiedDateAsc = 'modified_date_asc';
    case ModifiedDatedesc = 'modified_date_desc';
    case Random = 'random';
    case NameAsc = 'name_asc';
    case Namedesc = 'name_desc';
}
