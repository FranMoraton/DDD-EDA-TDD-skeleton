<?php

declare(strict_types=1);

namespace App\Tests\System\Domain\Criteria;

use App\System\Domain\Criteria\Direction;
use App\System\Domain\Criteria\Operator;
use App\Tests\System\Infrastructure\Dbal\TestCriteria;
use PHPUnit\Framework\TestCase;

class CriteriaTest extends TestCase
{
    public function testGivenValidFieldWhenAddFilterThenFilterIsAdded(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);

        $filters = $criteria->filters();

        $this->assertCount(1, $filters);
        $this->assertEquals('active', $filters['status']['value']);
        $this->assertEquals('=', $filters['status']['operator']);
    }

    public function testGivenInvalidFieldWhenAddFilterThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The field 'invalid_field' is not allowed.");

        $criteria = new TestCriteria();
        $criteria->withFilter('invalid_field', 'value', Operator::EQUALS);
    }

    public function testGivenJsonbArrayFieldWhenAddFilterThenFilterIsAdded(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);

        $filters = $criteria->filters();

        $this->assertCount(1, $filters);
        $this->assertEquals($date, $filters['slots[].activationDate']['value']);
        $this->assertEquals('>', $filters['slots[].activationDate']['operator']);
    }

    public function testGivenJsonbObjectFieldWhenAddFilterThenFilterIsAdded(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('config.model', 'gpt-4', Operator::EQUALS);

        $filters = $criteria->filters();

        $this->assertCount(1, $filters);
        $this->assertEquals('gpt-4', $filters['config.model']['value']);
        $this->assertEquals('=', $filters['config.model']['operator']);
    }

    public function testGivenInvalidJsonbFieldWhenAddFilterThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The field 'slots[].invalid_field' is not allowed.");

        $criteria = new TestCriteria();
        $criteria->withFilter('slots[].invalid_field', 'value', Operator::EQUALS);
    }

    public function testGivenInvalidTypeWhenAddFilterThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The field 'slots[].activationDate' must be of type 'datetime', 'string' was given.");

        $criteria = new TestCriteria();
        $criteria->withFilter('slots[].activationDate', 'not-a-date', Operator::GREATER_THAN);
    }

    public function testGivenPaginationWhenSetLimitAndOffsetThenValuesAreSet(): void
    {
        $criteria = new TestCriteria();
        $criteria->withLimit(25);
        $criteria->withOffset(50);

        $this->assertEquals(25, $criteria->limit());
        $this->assertEquals(50, $criteria->offset());
    }

    public function testGivenOrderWhenSetOrderThenOrderIsSet(): void
    {
        $criteria = new TestCriteria();
        $criteria->withOrder('created_at', Direction::DESC);

        $order = $criteria->order();

        $this->assertNotNull($order);
        $this->assertEquals('created_at', $order['field']);
        $this->assertEquals('DESC', $order['direction']);
    }

    public function testGivenMixedFiltersWhenAddFiltersThenAllFiltersAreAdded(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);
        $criteria->withFilter('slots[].code', 'SUMMER2025', Operator::EQUALS);
        $criteria->withFilter('config.model', 'gpt-4', Operator::EQUALS);

        $filters = $criteria->filters();

        $this->assertCount(4, $filters);
        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayHasKey('slots[].activationDate', $filters);
        $this->assertArrayHasKey('slots[].code', $filters);
        $this->assertArrayHasKey('config.model', $filters);
    }

    public function testGivenPageAndItemsWhenCalculatePaginationOffsetThenReturnCorrectOffset(): void
    {
        $criteria = new TestCriteria();

        $this->assertEquals(0, $criteria->calculatePaginationOffSet(1, 10));
        $this->assertEquals(10, $criteria->calculatePaginationOffSet(2, 10));
        $this->assertEquals(90, $criteria->calculatePaginationOffSet(10, 10));
        $this->assertEquals(0, $criteria->calculatePaginationOffSet(1, 50));
        $this->assertEquals(50, $criteria->calculatePaginationOffSet(2, 50));
    }

    public function testGivenValidFieldWhenAddInFilterThenFilterIsAdded(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', ['active', 'pending', 'processing'], Operator::IN);

        $filters = $criteria->filters();

        $this->assertCount(1, $filters);
        $this->assertEquals(['active', 'pending', 'processing'], $filters['status']['value']);
        $this->assertEquals('IN', $filters['status']['operator']);
    }

    public function testGivenInvalidFieldWhenAddInFilterThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The field 'invalid_field' is not allowed.");

        $criteria = new TestCriteria();
        $criteria->withFilter('invalid_field', ['value1', 'value2'], Operator::IN);
    }

    public function testGivenEmptyArrayWhenAddInFilterThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The values array for IN filter cannot be empty.");

        $criteria = new TestCriteria();
        $criteria->withFilter('status', [], Operator::IN);
    }

    public function testGivenInvalidValueTypeWhenAddInFilterThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The field 'status' must be of type 'string'");

        $criteria = new TestCriteria();
        $criteria->withFilter('status', ['active', 123], Operator::IN);
    }

    public function testGivenArrayValueWithNonInOperatorThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Array values are only allowed with IN operator.");

        $criteria = new TestCriteria();
        $criteria->withFilter('status', ['active', 'pending'], Operator::EQUALS);
    }

    public function testGivenNonArrayValueWithInOperatorThenThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("IN operator requires an array value.");

        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::IN);
    }

    public function testGivenJsonbArrayFieldWhenAddInFilterThenFilterIsAdded(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('slots[].status', ['active', 'pending'], Operator::IN);

        $filters = $criteria->filters();

        $this->assertCount(1, $filters);
        $this->assertEquals(['active', 'pending'], $filters['slots[].status']['value']);
        $this->assertEquals('IN', $filters['slots[].status']['operator']);
    }
}
