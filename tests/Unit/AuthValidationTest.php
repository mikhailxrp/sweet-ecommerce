<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once ROOT_PATH . '/src/Core/auth.php';

/**
 * Юнит-тесты чистых валидаторов форм авторизации (без БД и HTTP-контекста).
 */
final class AuthValidationTest extends TestCase
{
    public function testValidRegistrationPasses(): void
    {
        $errors = validateRegistration([
            'name'     => 'Анна',
            'email'    => 'anna@example.com',
            'password' => 'secret12',
        ]);

        $this->assertSame([], $errors);
    }

    public function testRegistrationRequiresAllFields(): void
    {
        $errors = validateRegistration(['name' => '', 'email' => '', 'password' => '']);

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testRegistrationRejectsMalformedEmail(): void
    {
        $errors = validateRegistration([
            'name'     => 'Анна',
            'email'    => 'not-an-email',
            'password' => 'secret12',
        ]);

        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayNotHasKey('password', $errors);
    }

    public function testRegistrationRejectsShortPassword(): void
    {
        $errors = validateRegistration([
            'name'     => 'Анна',
            'email'    => 'anna@example.com',
            'password' => 'short7',
        ]);

        $this->assertArrayHasKey('password', $errors);
    }

    public function testRegistrationAcceptsExactlyEightCharPassword(): void
    {
        $errors = validateRegistration([
            'name'     => 'Анна',
            'email'    => 'anna@example.com',
            'password' => '12345678',
        ]);

        $this->assertSame([], $errors);
    }

    public function testRegistrationRejectsOverlongName(): void
    {
        $errors = validateRegistration([
            'name'     => str_repeat('я', 101),
            'email'    => 'anna@example.com',
            'password' => 'secret12',
        ]);

        $this->assertArrayHasKey('name', $errors);
    }

    public function testLoginRequiresBothFields(): void
    {
        $errors = validateLogin(['email' => '', 'password' => '']);

        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testLoginPassesWithBothFieldsPresent(): void
    {
        // Валидатор входа проверяет только наличие полей — корректность
        // учётных данных сверяет контроллер (generic-ответ).
        $errors = validateLogin(['email' => 'whatever', 'password' => 'x']);

        $this->assertSame([], $errors);
    }
}
