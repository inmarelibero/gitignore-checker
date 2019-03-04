<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests\Model;

use Inmarelibero\GitIgnoreChecker\Model\GitIgnoreFile;
use Inmarelibero\GitIgnoreChecker\Model\GitIgnoreRule;
use Inmarelibero\GitIgnoreChecker\Model\RelativePath;
use Inmarelibero\GitIgnoreChecker\Model\Repository;
use Inmarelibero\GitIgnoreChecker\Tests\AbstractTestCase;

/**
 * Class RepositoryTest
 * @package Inmarelibero\GitIgnoreChecker\Tests\Model
 */
class RepositoryTest extends AbstractTestCase
{
    /**
     *
     */
    public function testConstruct()
    {
        $repositoryPath = $this->getTestRepositoryPath();
        $repository = new Repository($this->getTestRepositoryPath());

        $this->assertEquals($repositoryPath, $repository->getPath());
        $this->assertEquals($repositoryPath.'/', $repository->buildAbsolutePath('/'));
        $this->assertEquals($repositoryPath.'/foo', $repository->buildAbsolutePath('/foo'));
        $this->assertEquals($repositoryPath.'/.README', $repository->buildAbsolutePath('/.README'));
        $this->assertEquals($repositoryPath.'/foo/bar', $repository->buildAbsolutePath('/foo/bar'));
    }
}
