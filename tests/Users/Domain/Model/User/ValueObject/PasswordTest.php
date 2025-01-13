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
        Password::from('TestTest$');
    }

    public function testGivenPasswordWithoutCapsThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::from('test9876$');
    }

    public function testGivenPasswordWithoutMinusThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::from('TEST9876$');
    }

    public function testGivenPasswordSorterThanEightCharactersThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::from('T9$');
    }

    public function testGivenPasswordWithoutSpecialCharacterThenFail(): void
    {
        self::expectException(ValidationException::class);
        Password::from('Test9871');
    }

    public function testGivenRightFormatPasswordThenSuccess(): void
    {
        $password = Password::from('Test987$');
        self::assertInstanceOf(Password::class, $password);
    }

    public function testGivenSamePasswordWhenVerifyThenTrue(): void
    {
        $password = Password::from('Test987$');
        self::assertTrue($password->verify('Test987$'));
    }

    public function testGivenDifferentPasswordWhenVerifyThenFalse(): void
    {
        $password = Password::from('Test987$');
        self::assertFalse($password->verify('Test9876$'));
    }
}
