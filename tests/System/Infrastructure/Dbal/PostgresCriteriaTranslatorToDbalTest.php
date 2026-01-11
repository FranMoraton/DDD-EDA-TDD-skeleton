<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Dbal;

use App\System\Domain\Criteria\Direction;
use App\System\Domain\Criteria\Operator;
use App\System\Infrastructure\Dbal\PostgresCriteriaTranslatorToDbal;
use PHPUnit\Framework\TestCase;

class PostgresCriteriaTranslatorToDbalTest extends TestCase
{
    private PostgresCriteriaTranslatorToDbal $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = new PostgresCriteriaTranslatorToDbal();
    }

    public function testGivenSimpleFilterWhenTranslateToSqlThenReturnExpectedQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);

        $expectedSql = "SELECT * FROM discounts t WHERE t.status = 'active'";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenMultipleSimpleFiltersWhenTranslateToSqlThenReturnExpectedQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $criteria->withFilter('url', 'https://example.com', Operator::EQUALS);

        $expectedSql = "SELECT * FROM discounts t WHERE t.status = 'active' AND t.url = 'https://example.com'";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbArrayEqualsFilterWhenTranslateToSqlThenReturnContainsQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('slots[].status', 'active', Operator::EQUALS);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE t.slots @> '[{\"status\":\"active\"}]'::jsonb";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbArrayGreaterThanFilterWhenTranslateToSqlThenReturnExistsQuery(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbArrayLessThanFilterWhenTranslateToSqlThenReturnExistsQuery(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-12-31 23:59:59');
        $criteria->withFilter('slots[].deactivationDate', $date, Operator::LESS_THAN);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'deactivationDate')::text < '2025-12-31 23:59:59')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbArrayGreaterThanOrEqualsFilterWhenTranslateToSqlThenReturnExistsQuery(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-01-01 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN_OR_EQUALS);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text >= '2025-01-01 00:00:00')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbArrayLessThanOrEqualsFilterWhenTranslateToSqlThenReturnExistsQuery(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-06-30 23:59:59');
        $criteria->withFilter('slots[].deactivationDate', $date, Operator::LESS_THAN_OR_EQUALS);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'deactivationDate')::text <= '2025-06-30 23:59:59')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenMixedFiltersWhenTranslateToSqlThenReturnCombinedQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE t.status = 'active' AND EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenPaginationWhenTranslateToSqlThenReturnQueryWithLimitOffset(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $criteria->withLimit(10);
        $criteria->withOffset(20);

        $expectedSql = "SELECT * FROM discounts t WHERE t.status = 'active' LIMIT 10 OFFSET 20";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenOrderWhenTranslateToSqlThenReturnQueryWithOrderBy(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $criteria->withOrder('created_at', Direction::DESC);

        $expectedSql = "SELECT * FROM discounts t WHERE t.status = 'active' ORDER BY t.created_at DESC";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbFilterWithPaginationWhenTranslateToSqlThenReturnQueryWithDistinctAndPagination(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);
        $criteria->withLimit(50);
        $criteria->withOffset(0);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00') LIMIT 50 OFFSET 0";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenSimpleFilterWhenTranslateToCountSqlThenReturnCountQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);

        $expectedSql = "SELECT COUNT(*) as total_count FROM discounts t WHERE t.status = 'active'";
        $obtainedSql = $this->translator->translateToCountSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbFilterWhenTranslateToCountSqlThenReturnCountQueryWithExists(): void
    {
        $criteria = new TestCriteria();
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);

        $expectedSql = "SELECT COUNT(*) as total_count FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00')";
        $obtainedSql = $this->translator->translateToCountSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenMixedFiltersWhenTranslateToCountSqlThenReturnCountQueryWithAllConditions(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);

        $expectedSql = "SELECT COUNT(*) as total_count FROM discounts t WHERE t.status = 'active' AND EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00')";
        $obtainedSql = $this->translator->translateToCountSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenEmptyCriteriaWhenTranslateToSqlThenReturnSelectAllQuery(): void
    {
        $criteria = new TestCriteria();

        $expectedSql = "SELECT * FROM discounts t";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenEmptyCriteriaWhenTranslateToCountSqlThenReturnCountAllQuery(): void
    {
        $criteria = new TestCriteria();

        $expectedSql = "SELECT COUNT(*) as total_count FROM discounts t";
        $obtainedSql = $this->translator->translateToCountSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenMultipleJsonbFiltersWhenTranslateToSqlThenReturnQueryWithMultipleExists(): void
    {
        $criteria = new TestCriteria();
        $activationDate = new \DateTimeImmutable('2025-01-01 00:00:00');
        $deactivationDate = new \DateTimeImmutable('2025-12-31 23:59:59');

        $criteria->withFilter('slots[].activationDate', $activationDate, Operator::GREATER_THAN_OR_EQUALS);
        $criteria->withFilter('slots[].deactivationDate', $deactivationDate, Operator::LESS_THAN_OR_EQUALS);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text >= '2025-01-01 00:00:00') AND EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'deactivationDate')::text <= '2025-12-31 23:59:59')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbEqualsWithCodeFilterWhenTranslateToSqlThenReturnContainsQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('slots[].code', 'SUMMER2025', Operator::EQUALS);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE t.slots @> '[{\"code\":\"SUMMER2025\"}]'::jsonb";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenFullQueryWithAllOptionsWhenTranslateToSqlThenReturnCompleteQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $criteria->withFilter('url', 'https://example.com', Operator::LIKE);
        $activationDate = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $activationDate, Operator::GREATER_THAN);
        $criteria->withOrder('created_at', Direction::DESC);
        $criteria->withLimit(25);
        $criteria->withOffset(50);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE t.status = 'active' AND t.url LIKE 'https://example.com' AND EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00') ORDER BY t.created_at DESC LIMIT 25 OFFSET 50";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbObjectEqualsFilterWhenTranslateToSqlThenReturnJsonbAccessQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('config.model', 'gpt-4', Operator::EQUALS);

        $expectedSql = "SELECT * FROM discounts t WHERE (t.config->>'model') = 'gpt-4'";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbObjectComparisonFilterWhenTranslateToSqlThenReturnJsonbAccessQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('config.timeout', 30, Operator::GREATER_THAN);

        $expectedSql = "SELECT * FROM discounts t WHERE (t.config->>'timeout')::text > 30";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbObjectFilterWhenTranslateToCountSqlThenReturnCountQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('config.model', 'gpt-4', Operator::EQUALS);

        $expectedSql = "SELECT COUNT(*) as total_count FROM discounts t WHERE (t.config->>'model') = 'gpt-4'";
        $obtainedSql = $this->translator->translateToCountSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenMixedAllTypesFiltersWhenTranslateToSqlThenReturnCompleteQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', 'active', Operator::EQUALS);
        $criteria->withFilter('config.model', 'gpt-4', Operator::EQUALS);
        $date = new \DateTimeImmutable('2025-09-12 00:00:00');
        $criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE t.status = 'active' AND (t.config->>'model') = 'gpt-4' AND EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'activationDate')::text > '2025-09-12 00:00:00')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenSimpleInFilterWhenTranslateToSqlThenReturnInQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', ['active', 'pending', 'processing'], Operator::IN);

        $expectedSql = "SELECT * FROM discounts t WHERE t.status IN ('active', 'pending', 'processing')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbObjectInFilterWhenTranslateToSqlThenReturnInQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('config.model', ['gpt-4', 'gpt-3.5', 'claude'], Operator::IN);

        $expectedSql = "SELECT * FROM discounts t WHERE (t.config->>'model') IN ('gpt-4', 'gpt-3.5', 'claude')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenJsonbArrayInFilterWhenTranslateToSqlThenReturnExistsWithInQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('slots[].status', ['active', 'pending'], Operator::IN);

        $expectedSql = "SELECT DISTINCT t.* FROM discounts t WHERE EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) AS elem WHERE (elem->>'status') IN ('active', 'pending'))";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenSimpleInFilterWhenTranslateToCountSqlThenReturnCountQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('status', ['active', 'pending'], Operator::IN);

        $expectedSql = "SELECT COUNT(*) as total_count FROM discounts t WHERE t.status IN ('active', 'pending')";
        $obtainedSql = $this->translator->translateToCountSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }

    public function testGivenMixedFiltersWithInWhenTranslateToSqlThenReturnCombinedQuery(): void
    {
        $criteria = new TestCriteria();
        $criteria->withFilter('url', 'https://example.com', Operator::EQUALS);
        $criteria->withFilter('status', ['active', 'pending'], Operator::IN);

        $expectedSql = "SELECT * FROM discounts t WHERE t.url = 'https://example.com' AND t.status IN ('active', 'pending')";
        $obtainedSql = $this->translator->translateToSql($criteria, 'discounts');

        $this->assertEquals($expectedSql, $obtainedSql);
    }
}
