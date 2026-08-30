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
}
