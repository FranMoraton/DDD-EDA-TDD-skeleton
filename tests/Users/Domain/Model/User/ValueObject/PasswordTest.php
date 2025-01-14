<?php

namespace App\Tests\Users\Domain\Model\User\ValueObject;

use App\System\Domain\Exception\ValidationException;
use App\Users\Domain\Model\User\ValueObject\Password;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    public function testGivenPasswordWithoutNumberThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::create('TestTest$');
    }

    public function testGivenPasswordWithoutCapsThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::create('test9876$');
    }

    public function testGivenPasswordWithoutMinusThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::create('TEST9876$');
    }

    public function testGivenPasswordSorterThanEightCharactersThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::create('T9$');
    }

    public function testGivenPasswordWithoutSpecialCharacterThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::create('Test9871');
    }

    public function testGivenRightFormatPasswordThenSuccess(): void
    {
        $password = Password::create('Test987$');
        self::assertInstanceOf(Password::class, $password);
    }

    public function testGivenSamePasswordWhenVerifyThenTrue(): void
    {
        $password = Password::create('Test987$');
        self::assertTrue($password->verify('Test987$'));
    }

    public function testGivenDifferentPasswordWhenVerifyThenFalse(): void
    {
        $password = Password::create('Test987$');
        self::assertFalse($password->verify('Test9876$'));
    }
}
