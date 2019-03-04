<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests;

use Inmarelibero\GitIgnoreChecker\GitIgnoreChecker;
use Inmarelibero\GitIgnoreChecker\Model\Repository;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTestCase
 * @package Inmarelibero\GitIgnoreChecker\Tests
 */
class AbstractTestCase extends TestCase
{
    /**
     * @var GitIgnoreChecker
     */
    protected $gitIgnoreChecker;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->gitIgnoreChecker = new GitIgnoreChecker(__DIR__.'/test_repository');
    }

    /**
     * @return string
     */
    protected function getTestRepositoryPath()
    {
        return realpath(__DIR__.'/test_repository');
    }

    /**
     * @return Repository
     */
    protected function getTestRepository()
    {
        return new Repository(
            realpath($this->getTestRepositoryPath())
        );
    }
}