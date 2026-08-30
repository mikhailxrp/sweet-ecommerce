<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testNormalizeUserIdAcceptsPositiveInt(): void
    {
        $this->assertSame(5, normalizeUserId(5));
    }

    public function testNormalizeUserIdAcceptsNumericString(): void
    {
        $this->assertSame(5, normalizeUserId('5'));
    }

    public function testNormalizeUserIdRejectsZeroAndNegative(): void
    {
        $this->assertNull(normalizeUserId(0));
        $this->assertNull(normalizeUserId(-1));
    }

    public function testNormalizeUserIdRejectsNonNumericInput(): void
    {
        $this->assertNull(normalizeUserId('abc'));
        $this->assertNull(normalizeUserId(null));
    }

    public function testEEscapesHtmlSpecialChars(): void
    {
        $this->assertSame('&lt;script&gt;', e('<script>'));
    }

    public function testCsrfTokenIsStableWithinSameSession(): void
    {
        $token = csrfToken();

        $this->assertSame($token, csrfToken());
    }

    public function testVerifyCsrfTokenAcceptsMatchingToken(): void
    {
        $token = csrfToken();

        $this->assertTrue(verifyCsrfToken($token));
    }

    public function testVerifyCsrfTokenRejectsWrongToken(): void
    {
        csrfToken();

        $this->assertFalse(verifyCsrfToken('wrong-token'));
    }

    public function testVerifyCsrfTokenRejectsEmptyOrMissingToken(): void
    {
        csrfToken();

        $this->assertFalse(verifyCsrfToken(''));
        $this->assertFalse(verifyCsrfToken(null));
    }

    public function testRegenerateSessionChangesIdAndClearsCsrfToken(): void
    {
        ensureSessionStarted();
        $oldId = session_id();
        csrfToken();

        regenerateSession();

        $this->assertNotSame($oldId, session_id());
        $this->assertArrayNotHasKey('csrf_token', $_SESSION);
    }

    public function testSlugifyTransliteratesCyrillic(): void
    {
        $this->assertSame('medovik', slugify('Медовик'));
    }

    public function testSlugifyLowercasesLatinInput(): void
    {
        $this->assertSame('cupcakestudio', slugify('CupcakeStudio'));
    }

    public function testSlugifyCollapsesPunctuationAndSpacesIntoOneHyphen(): void
    {
        $this->assertSame('tort-edinorog', slugify('Торт «Единорог»'));
    }

    public function testSlugifyKeepsDigits(): void
    {
        $this->assertSame('nabor-makarun-12-sht', slugify('Набор макарун (12 шт)'));
    }

    public function testSlugifyTrimsLeadingAndTrailingHyphens(): void
    {
        $this->assertSame('privet', slugify('  привет!!!  '));
    }

    public function testSlugifyReturnsEmptyStringForOnlyPunctuation(): void
    {
        $this->assertSame('', slugify('---'));
    }

    public function testFormatPriceAddsRubleSignAndSpacedThousands(): void
    {
        $this->assertSame('12 345 ₽', formatPrice('12345.00'));
    }

    public function testFormatPriceDropsKopecksFromWholeAmount(): void
    {
        $this->assertSame('90 ₽', formatPrice('90.00'));
    }

    public function testFormatPriceRoundsFractionalKopecks(): void
    {
        $this->assertSame('1 251 ₽', formatPrice('1250.50'));
    }

    public function testFormatPriceHandlesZero(): void
    {
        $this->assertSame('0 ₽', formatPrice('0.00'));
    }
}
