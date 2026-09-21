<?php

namespace ClassKit\Tests;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Api\Enum\RequestMethod;
use ClassKit\Entity\Core\UpdatedGuidEntity;
use ClassKit\Page\AjaxPage\AjaxPageConfig;
use ClassKit\Page\AjaxPage\AjaxPageRequest;
use ClassKit\Page\AjaxPage\Enums\SortOrder;
use ClassKit\Page\AjaxPage\AjaxPageResponse;

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

    public function testSortOrderEnumValues(): void
    {
        $this->assertSame('sitemap_asc', SortOrder::SitemapAsc->value);
        $this->assertSame('sitemap_desc', SortOrder::SitemapDesc->value);
        $this->assertSame('date_asc', SortOrder::DateAsc->value);
        $this->assertSame('date_desc', SortOrder::DateDesc->value);
        $this->assertSame('modified_date_asc', SortOrder::ModifiedDateAsc->value);
        $this->assertSame('modified_date_desc', SortOrder::ModifiedDatedesc->value);
        $this->assertSame('random', SortOrder::Random->value);
        $this->assertSame('name_asc', SortOrder::NameAsc->value);
        $this->assertSame('name_desc', SortOrder::Namedesc->value);
        $this->assertCount(9, SortOrder::cases());
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

    public function testUpdatedGuidEntityAllowsSettingCreatedAndUpdatedDates(): void
    {
        $entity = new class extends UpdatedGuidEntity {
            public function __construct()
            {
                parent::__construct();
            }
        };

        $created = new DateTimeImmutable('2024-01-01 12:00:00');
        $updated = new DateTimeImmutable('2024-01-02 12:00:00');

        $entity->setDateCreated($created);
        $entity->setDateUpdated($updated);

        $this->assertSame($created, $entity->getDateCreated());
        $this->assertSame($updated, $entity->getDateUpdated());
        $this->assertSame('01/01/2024 12:00', $entity->getDateCreatedString());
        $this->assertSame('02/01/2024 12:00', $entity->getDateUpdatedString());
    }

    public function testAjaxPageRequestRejectsUnknownSortOrder(): void
    {
        $this->expectException(\ValueError::class);

        AjaxPageRequest::fromArray([
            'pageNum' => 1,
            'sortOrder' => 'not-a-valid-sort-order',
        ]);
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
