<?php

namespace ClassKit\Tests;

use ClassKit\Api\Enum\RequestMethod;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Page\AjaxPage\AjaxPageConfig;
use ClassKit\Page\AjaxPage\AjaxPageRequest;
use ClassKit\Page\AjaxPage\AjaxPageResponse;
use ClassKit\Page\AjaxPage\Enums\SortOrder;
use PHPUnit\Framework\TestCase;

final class ClassKitPackageTest extends TestCase
{
    public function testRequestMethodEnumValues(): void
    {
        $this->assertSame('GET', RequestMethod::GET->value);
        $this->assertSame('POST', RequestMethod::POST->value);
        $this->assertSame('PUT', RequestMethod::PUT->value);
        $this->assertSame('DELETE', RequestMethod::DELETE->value);
        $this->assertSame('PATCH', RequestMethod::PATCH->value);
        $this->assertCount(5, RequestMethod::cases());
    }

    public function testResponseTypeEnumValues(): void
    {
        $this->assertSame('json', ResponseType::JSON->value);
        $this->assertSame('xml', ResponseType::XML->value);
        $this->assertCount(2, ResponseType::cases());
    }

    public function testAjaxPageRequestFromArrayAndToArray(): void
    {
        $request = AjaxPageRequest::fromArray([
            'pageNum' => 3,
            'perPage' => 12,
            'sortOrder' => SortOrder::DateDesc->value,
        ]);

        $this->assertSame(3, $request->pageNum);
        $this->assertSame(12, $request->perPage);
        $this->assertSame(SortOrder::DateDesc, $request->sortOrder);
        $this->assertSame([
            'pageNum' => 3,
            'perPage' => 12,
            'sortOrder' => SortOrder::DateDesc,
        ], $request->toArray());
    }

    public function testAjaxPageRequestDefaultsMissingValuesToNull(): void
    {
        $request = AjaxPageRequest::fromArray([
            'pageNum' => 2,
        ]);

        $this->assertSame(2, $request->pageNum);
        $this->assertNull($request->perPage);
        $this->assertNull($request->sortOrder);
        $this->assertSame([
            'pageNum' => 2,
            'perPage' => null,
            'sortOrder' => null,
        ], $request->toArray());
    }

    public function testAjaxPageResponseToArray(): void
    {
        $response = new AjaxPageResponse(
            pages: [],
            html: '<div class="card">Result</div>',
            nextPageNum: 2,
            hasNextPage: true,
        );

        $this->assertSame([
            'pages' => [],
            'html' => '<div class="card">Result</div>',
            'nextPageNum' => 2,
            'hasNextPage' => true,
        ], $response->toArray());
    }

    public function testAjaxPageConfigSerializesValues(): void
    {
        $config = new AjaxPageConfig(
            startPage: 1,
            perPage: 10,
            sortOrder: SortOrder::NameAsc,
            cardPath: 'cards/item.php',
            pl: null,
            parent: null,
            debug: true,
            includeExclusions: false,
            noResultsMessage: 'No results',
            pkg: null,
        );

        $this->assertSame([
            'startPage' => 1,
            'perPage' => 10,
            'sortOrder' => SortOrder::NameAsc,
            'cardPath' => 'cards/item.php',
            'pageList' => null,
            'parentPage' => null,
            'debug' => true,
            'includeExclusions' => false,
            'noResultsMessage' => 'No results',
            'pkg' => null,
        ], $config->toArray());
    }
}
