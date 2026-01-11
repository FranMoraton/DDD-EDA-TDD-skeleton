<?php

declare(strict_types=1);

namespace App\Tests\System\Domain\Criteria;

use App\System\Domain\Criteria\FilterParser;
use App\System\Domain\Criteria\Operator;
use App\Tests\System\Infrastructure\Dbal\TestCriteria;
use PHPUnit\Framework\TestCase;

class FilterParserTest extends TestCase
{
    public function testGivenSimpleValueWhenApplyFiltersThenUsesEqualsOperator(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'active']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('active', $filters['status']['value']);
        $this->assertEquals(Operator::EQUALS->value, $filters['status']['operator']);
    }

    public function testGivenExplicitEqualsWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'EQUALS::active']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('active', $filters['status']['value']);
        $this->assertEquals(Operator::EQUALS->value, $filters['status']['operator']);
    }

    public function testGivenInOperatorWhenApplyFiltersThenParsesCommaSeparatedValues(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'IN::active,pending,processing']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(['active', 'pending', 'processing'], $filters['status']['value']);
        $this->assertEquals(Operator::IN->value, $filters['status']['operator']);
    }

    public function testGivenGreaterThanOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['created_at' => 'GREATER_THAN::2025-01-01']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(\DateTimeImmutable::class, $filters['created_at']['value']);
        $this->assertEquals('2025-01-01', $filters['created_at']['value']->format('Y-m-d'));
        $this->assertEquals(Operator::GREATER_THAN->value, $filters['created_at']['operator']);
    }

    public function testGivenLessThanOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['created_at' => 'LESS_THAN::2025-12-31']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(Operator::LESS_THAN->value, $filters['created_at']['operator']);
    }

    public function testGivenGreaterThanOrEqualsOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.timeout' => 'GREATER_THAN_OR_EQUALS::10']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(10, $filters['config.timeout']['value']);
        $this->assertEquals(Operator::GREATER_THAN_OR_EQUALS->value, $filters['config.timeout']['operator']);
    }

    public function testGivenLessThanOrEqualsOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.timeout' => 'LESS_THAN_OR_EQUALS::100']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(100, $filters['config.timeout']['value']);
        $this->assertEquals(Operator::LESS_THAN_OR_EQUALS->value, $filters['config.timeout']['operator']);
    }

    public function testGivenNotEqualsOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'NOT_EQUALS::deleted']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('deleted', $filters['status']['value']);
        $this->assertEquals(Operator::NOT_EQUALS->value, $filters['status']['operator']);
    }

    public function testGivenLikeOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['url' => 'LIKE::%example%']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('%example%', $filters['url']['value']);
        $this->assertEquals(Operator::LIKE->value, $filters['url']['operator']);
    }

    public function testGivenUnknownOperatorWhenApplyFiltersThenTreatsAsEqualsWithFullValue(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'UNKNOWN_OP::value']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('UNKNOWN_OP::value', $filters['status']['value']);
        $this->assertEquals(Operator::EQUALS->value, $filters['status']['operator']);
    }

    public function testGivenUnknownFieldWhenApplyFiltersThenSkipsIt(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, [
            'status' => 'active',
            'unknown_field' => 'value',
        ]);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayNotHasKey('unknown_field', $filters);
    }

    public function testGivenNullValueWhenApplyFiltersThenSkipsIt(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, [
            'status' => 'active',
            'url' => null,
        ]);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayNotHasKey('url', $filters);
    }

    public function testGivenEmptyStringWhenApplyFiltersThenSkipsIt(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, [
            'status' => 'active',
            'url' => '',
        ]);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayNotHasKey('url', $filters);
    }

    public function testGivenIntTypeWhenApplyFiltersThenCastsToInt(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.timeout' => '42']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertSame(42, $filters['config.timeout']['value']);
    }

    public function testGivenDatetimeTypeWhenApplyFiltersThenCastsToDateTimeImmutable(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['created_at' => '2025-06-15T10:30:00+00:00']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(\DateTimeImmutable::class, $filters['created_at']['value']);
        $this->assertEquals('2025-06-15', $filters['created_at']['value']->format('Y-m-d'));
    }

    public function testGivenJsonbArrayFieldWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['slots[].status' => 'active']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('active', $filters['slots[].status']['value']);
        $this->assertEquals(Operator::EQUALS->value, $filters['slots[].status']['operator']);
    }

    public function testGivenJsonbArrayFieldWithInOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['slots[].status' => 'IN::active,pending']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(['active', 'pending'], $filters['slots[].status']['value']);
        $this->assertEquals(Operator::IN->value, $filters['slots[].status']['operator']);
    }

    public function testGivenJsonbArrayDatetimeFieldWhenApplyFiltersThenCastsCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, [
            'slots[].activationDate' => 'GREATER_THAN::2025-01-01',
        ]);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(\DateTimeImmutable::class, $filters['slots[].activationDate']['value']);
        $this->assertEquals(Operator::GREATER_THAN->value, $filters['slots[].activationDate']['operator']);
    }

    public function testGivenMultipleFiltersWhenApplyFiltersThenAppliesAll(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, [
            'status' => 'active',
            'url' => 'LIKE::%example%',
            'config.timeout' => 'GREATER_THAN::10',
            'slots[].status' => 'IN::active,pending',
        ]);

        $filters = $criteria->filters();
        $this->assertCount(4, $filters);
        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayHasKey('url', $filters);
        $this->assertArrayHasKey('config.timeout', $filters);
        $this->assertArrayHasKey('slots[].status', $filters);
    }

    public function testGivenLowercaseOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'in::active,pending']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(['active', 'pending'], $filters['status']['value']);
        $this->assertEquals(Operator::IN->value, $filters['status']['operator']);
    }

    public function testGivenMixedCaseOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'Greater_Than::value']);

        // Note: 'value' is not a valid datetime, but we're testing the operator parsing
        // The actual value will cause an exception when castValue is called
        // For this test, let's use a string field
        $criteria = new TestCriteria();
        FilterParser::applyFilters($criteria, ['status' => 'Not_Equals::deleted']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('deleted', $filters['status']['value']);
        $this->assertEquals(Operator::NOT_EQUALS->value, $filters['status']['operator']);
    }

    public function testGivenInWithSpacesAroundValuesWhenApplyFiltersThenTrimsValues(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['status' => 'IN::active , pending , processing']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(['active', 'pending', 'processing'], $filters['status']['value']);
    }

    public function testGivenValueWithColonsWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['url' => 'EQUALS::https://example.com:8080/path']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals('https://example.com:8080/path', $filters['url']['value']);
    }

    public function testGivenEmptyFiltersArrayWhenApplyFiltersThenDoesNothing(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, []);

        $filters = $criteria->filters();
        $this->assertCount(0, $filters);
    }

    public function testGivenBoolTypeWithTrueStringWhenApplyFiltersThenCastsToBool(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.enabled' => 'true']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertTrue($filters['config.enabled']['value']);
    }

    public function testGivenBoolTypeWithFalseStringWhenApplyFiltersThenCastsToBool(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.enabled' => 'false']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertFalse($filters['config.enabled']['value']);
    }

    public function testGivenBoolTypeWith1StringWhenApplyFiltersThenCastsToBool(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.enabled' => '1']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertTrue($filters['config.enabled']['value']);
    }

    public function testGivenBoolTypeWith0StringWhenApplyFiltersThenCastsToBool(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.enabled' => '0']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertFalse($filters['config.enabled']['value']);
    }

    public function testGivenFloatTypeWhenApplyFiltersThenCastsToFloat(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.rate' => '3.14']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertSame(3.14, $filters['config.rate']['value']);
    }

    public function testGivenFloatTypeWithOperatorWhenApplyFiltersThenCastsCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.rate' => 'GREATER_THAN::2.5']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertSame(2.5, $filters['config.rate']['value']);
        $this->assertEquals(Operator::GREATER_THAN->value, $filters['config.rate']['operator']);
    }

    public function testGivenIsNullOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['url' => 'IS_NULL']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(Operator::IS_NULL->value, $filters['url']['operator']);
    }

    public function testGivenIsNotNullOperatorWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['url' => 'IS_NOT_NULL']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(Operator::IS_NOT_NULL->value, $filters['url']['operator']);
    }

    public function testGivenIsNullLowercaseWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['url' => 'is_null']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(Operator::IS_NULL->value, $filters['url']['operator']);
    }

    public function testGivenJsonbFieldWithIsNullWhenApplyFiltersThenParsesCorrectly(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, ['config.model' => 'IS_NULL']);

        $filters = $criteria->filters();
        $this->assertCount(1, $filters);
        $this->assertEquals(Operator::IS_NULL->value, $filters['config.model']['operator']);
    }

    public function testGivenMultipleFiltersWithIsNullWhenApplyFiltersThenAppliesAll(): void
    {
        $criteria = new TestCriteria();

        FilterParser::applyFilters($criteria, [
            'status' => 'active',
            'url' => 'IS_NULL',
            'config.model' => 'IS_NOT_NULL',
        ]);

        $filters = $criteria->filters();
        $this->assertCount(3, $filters);
        $this->assertEquals(Operator::EQUALS->value, $filters['status']['operator']);
        $this->assertEquals(Operator::IS_NULL->value, $filters['url']['operator']);
        $this->assertEquals(Operator::IS_NOT_NULL->value, $filters['config.model']['operator']);
    }
}
