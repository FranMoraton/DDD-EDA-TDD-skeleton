<?php

namespace App\Tests\System\Infrastructure\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Contracts\Cache\CacheInterface;

final class FeatureContext implements Context
{
    private static CacheInterface $contextCacheAdapter;

    public function __construct(
        private readonly Connection $connection,
        CacheInterface $contextCacheAdapter,
    ) {
        self::$contextCacheAdapter = $contextCacheAdapter;
    }

    /**
     * @BeforeSuite
     */
    public static function beforeSuite(BeforeSuiteScope $scope): void
    {
        self::$contextCacheAdapter->clear();
        self::cleanEnvironment();
    }

    /**
     * @BeforeScenario
     * @throws Exception
     */
    public function beforeScenario(BeforeScenarioScope $scope): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function afterScenario(AfterScenarioScope $scope): void
    {
        $this->connection->rollBack();
    }

    /**
     * @Given the environment is clean
     */
    public static function cleanEnvironment(): void
    {
    }
}
