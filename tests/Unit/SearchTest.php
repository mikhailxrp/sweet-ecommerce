<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SearchTest extends TestCase
{
    // ─── normalizeSearchQuery() ─────────────────────────────────────────

    public function testNormalizeSearchQueryTrimsSurroundingWhitespace(): void
    {
        $this->assertSame('торт', normalizeSearchQuery('  торт  '));
    }

    public function testNormalizeSearchQueryCollapsesRepeatedInternalWhitespace(): void
    {
        $this->assertSame('шоколадный торт', normalizeSearchQuery("шоколадный\t\n  торт"));
    }

    public function testNormalizeSearchQueryKeepsCyrillicIntact(): void
    {
        $this->assertSame('капкейк с вишней', normalizeSearchQuery('капкейк с вишней'));
    }

    public function testNormalizeSearchQueryReturnsEmptyStringForWhitespaceOnly(): void
    {
        $this->assertSame('', normalizeSearchQuery('   '));
    }

    public function testNormalizeSearchQueryTruncatesOverlongInput(): void
    {
        $result = normalizeSearchQuery(str_repeat('а', 500));

        $this->assertSame(100, mb_strlen($result));
    }

    // ─── resolveSearchStrategy() ────────────────────────────────────────

    public function testResolveSearchStrategyReturnsEmptyForEmptyString(): void
    {
        $this->assertSame('empty', resolveSearchStrategy(''));
    }

    public function testResolveSearchStrategyReturnsPrefixForOneCharacter(): void
    {
        $this->assertSame('prefix', resolveSearchStrategy('т'));
    }

    public function testResolveSearchStrategyReturnsPrefixForTwoCyrillicCharacters(): void
    {
        $this->assertSame('prefix', resolveSearchStrategy('аб'));
    }

    public function testResolveSearchStrategyReturnsFulltextForThreeCharacters(): void
    {
        $this->assertSame('fulltext', resolveSearchStrategy('аб' . 'в'));
    }

    public function testResolveSearchStrategyReturnsFulltextForLongQuery(): void
    {
        $this->assertSame('fulltext', resolveSearchStrategy('шоколадный торт'));
    }
}
