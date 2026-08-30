<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CatalogTest extends TestCase
{
    // ─── resolveSortKey() ───────────────────────────────────────────────

    public function testResolveSortKeyAcceptsWhitelistedValue(): void
    {
        $this->assertSame('price_asc', resolveSortKey('price_asc'));
    }

    public function testResolveSortKeyFallsBackToPopularForUnknownValue(): void
    {
        $this->assertSame('popular', resolveSortKey('DROP TABLE products;--'));
    }

    public function testResolveSortKeyFallsBackToPopularForEmptyString(): void
    {
        $this->assertSame('popular', resolveSortKey(''));
    }

    // ─── sortSql() ──────────────────────────────────────────────────────

    public function testSortSqlMapsKnownKeyToOrderByFragment(): void
    {
        $this->assertSame('p.price ASC', sortSql('price_asc'));
    }

    public function testSortSqlFallsBackToPopularForUnknownKey(): void
    {
        $this->assertSame('p.sales_count DESC', sortSql('unknown'));
    }

    // ─── resolvePage() ──────────────────────────────────────────────────

    public function testResolvePageAcceptsPositiveNumericString(): void
    {
        $this->assertSame(3, resolvePage('3'));
    }

    public function testResolvePageRejectsZero(): void
    {
        $this->assertSame(1, resolvePage('0'));
    }

    public function testResolvePageRejectsNonNumericString(): void
    {
        $this->assertSame(1, resolvePage('abc'));
    }

    public function testResolvePageRejectsNegativeNumber(): void
    {
        $this->assertSame(1, resolvePage('-5'));
    }

    public function testResolvePageRejectsEmptyString(): void
    {
        $this->assertSame(1, resolvePage(''));
    }

    // ─── totalPages() ───────────────────────────────────────────────────

    public function testTotalPagesRoundsUp(): void
    {
        $this->assertSame(4, totalPages(10, 3));
    }

    public function testTotalPagesExactDivision(): void
    {
        $this->assertSame(2, totalPages(6, 3));
    }

    public function testTotalPagesReturnsOneForZeroItems(): void
    {
        $this->assertSame(1, totalPages(0, 24));
    }

    // ─── buildQuery() ───────────────────────────────────────────────────

    public function testBuildQueryMergesOverrideIntoCurrentQuery(): void
    {
        $this->assertSame('?sort=new&page=2', buildQuery(['sort' => 'new'], ['page' => 2]));
    }

    public function testBuildQueryRemovesKeyWhenOverrideIsNull(): void
    {
        $this->assertSame('?sort=new', buildQuery(['sort' => 'new', 'page' => 3], ['page' => null]));
    }

    public function testBuildQueryReturnsEmptyStringWhenNothingLeft(): void
    {
        $this->assertSame('', buildQuery(['page' => 3], ['page' => null]));
    }

    // ─── parseFilters() ─────────────────────────────────────────────────

    public function testParseFiltersReturnsDefaultsForEmptyQuery(): void
    {
        $this->assertSame(
            [
                'price_min'     => null,
                'price_max'     => null,
                'vendor_ids'    => [],
                'in_stock'      => false,
                'weight_bucket' => null,
                'sugar_free'    => false,
                'min_rating'    => null,
            ],
            parseFilters([])
        );
    }

    public function testParseFiltersAcceptsValidPriceRange(): void
    {
        $filters = parseFilters(['price_min' => '100', 'price_max' => '500']);

        $this->assertSame('100.00', $filters['price_min']);
        $this->assertSame('500.00', $filters['price_max']);
    }

    public function testParseFiltersSwapsInvertedPriceRange(): void
    {
        $filters = parseFilters(['price_min' => '500', 'price_max' => '100']);

        $this->assertSame('100.00', $filters['price_min']);
        $this->assertSame('500.00', $filters['price_max']);
    }

    public function testParseFiltersRejectsNonNumericPrice(): void
    {
        $filters = parseFilters(['price_min' => 'abc', 'price_max' => 'DROP TABLE products;--']);

        $this->assertNull($filters['price_min']);
        $this->assertNull($filters['price_max']);
    }

    public function testParseFiltersRejectsNegativePrice(): void
    {
        $this->assertNull(parseFilters(['price_min' => '-50'])['price_min']);
    }

    public function testParseFiltersKeepsOnlyPositiveIntegerVendorIds(): void
    {
        $filters = parseFilters(['vendor' => ['3', '7', 'abc', '-1', '0', 3]]);

        $this->assertSame([3, 7], $filters['vendor_ids']);
    }

    public function testParseFiltersIgnoresVendorWhenNotArray(): void
    {
        $this->assertSame([], parseFilters(['vendor' => '99999999999'])['vendor_ids']);
    }

    public function testParseFiltersAcceptsInStockFlag(): void
    {
        $this->assertTrue(parseFilters(['in_stock' => '1'])['in_stock']);
    }

    public function testParseFiltersRejectsNonStrictInStockValue(): void
    {
        $this->assertFalse(parseFilters(['in_stock' => 'yes'])['in_stock']);
    }

    public function testParseFiltersAcceptsWhitelistedWeightBucket(): void
    {
        $this->assertSame('500to1000', parseFilters(['weight' => '500to1000'])['weight_bucket']);
    }

    public function testParseFiltersRejectsUnknownWeightBucket(): void
    {
        $this->assertNull(parseFilters(['weight' => 'huge'])['weight_bucket']);
    }

    public function testParseFiltersAcceptsSugarFreeFlag(): void
    {
        $this->assertTrue(parseFilters(['sugar_free' => '1'])['sugar_free']);
    }

    public function testParseFiltersTakesMinimumOfCheckedRatings(): void
    {
        $this->assertSame(3, parseFilters(['rating' => ['5', '3', '4']])['min_rating']);
    }

    public function testParseFiltersRejectsOutOfRangeRating(): void
    {
        $this->assertNull(parseFilters(['rating' => ['99', '0']])['min_rating']);
    }

    // ─── filterConditions() ─────────────────────────────────────────────

    public function testFilterConditionsReturnsEmptyForNoFilters(): void
    {
        $this->assertSame(['sql' => [], 'params' => []], filterConditions(parseFilters([])));
    }

    public function testFilterConditionsBuildsPriceRangeCondition(): void
    {
        $conditions = filterConditions(parseFilters(['price_min' => '100', 'price_max' => '500']));

        $this->assertSame(['p.price >= ?', 'p.price <= ?'], $conditions['sql']);
        $this->assertSame(
            [
                ['value' => '100.00', 'type' => \PDO::PARAM_STR],
                ['value' => '500.00', 'type' => \PDO::PARAM_STR],
            ],
            $conditions['params']
        );
    }

    public function testFilterConditionsBuildsVendorInClauseWithBoundParams(): void
    {
        $conditions = filterConditions(parseFilters(['vendor' => ['3', '7']]));

        $this->assertSame(['p.vendor_id IN (?,?)'], $conditions['sql']);
        $this->assertSame(
            [
                ['value' => 3, 'type' => \PDO::PARAM_INT],
                ['value' => 7, 'type' => \PDO::PARAM_INT],
            ],
            $conditions['params']
        );
    }

    public function testFilterConditionsAddsInStockClauseWithoutParams(): void
    {
        $conditions = filterConditions(parseFilters(['in_stock' => '1']));

        $this->assertCount(1, $conditions['sql']);
        $this->assertStringContainsString('has_variants', $conditions['sql'][0]);
        $this->assertSame([], $conditions['params']);
    }

    public function testFilterConditionsAddsSugarFreeClause(): void
    {
        $this->assertSame(['p.is_sugar_free = 1'], filterConditions(parseFilters(['sugar_free' => '1']))['sql']);
    }

    public function testFilterConditionsAddsMinRatingClauseWithBoundParam(): void
    {
        $conditions = filterConditions(parseFilters(['rating' => ['4']]));

        $this->assertSame(['p.rating_avg >= ?'], $conditions['sql']);
        $this->assertSame([['value' => 4, 'type' => \PDO::PARAM_INT]], $conditions['params']);
    }

    // ─── resolveView() ──────────────────────────────────────────────────

    public function testResolveViewAcceptsList(): void
    {
        $this->assertSame('list', resolveView('list'));
    }

    public function testResolveViewFallsBackToGridForUnknownValue(): void
    {
        $this->assertSame('grid', resolveView('whatever'));
    }

    public function testResolveViewFallsBackToGridForEmptyString(): void
    {
        $this->assertSame('grid', resolveView(''));
    }
}
