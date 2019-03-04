<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests\Model;

use Inmarelibero\GitIgnoreChecker\Model\GitIgnoreFile;
use Inmarelibero\GitIgnoreChecker\Model\RelativePath;
use Inmarelibero\GitIgnoreChecker\Tests\AbstractTestCase;

/**
 * Class GitIgnoreFileTest
 * @package Inmarelibero\GitIgnoreChecker\Tests\Model
 */
class GitIgnoreFileTest extends AbstractTestCase
{
    /**
     *
     */
    public function testConstruct()
    {
        $repository = $this->getTestRepository();

        $gitIgnoreFile = new GitIgnoreFile(new RelativePath($repository, '/'));
        $this->assertInstanceOf(GitIgnoreFile::class, $gitIgnoreFile);

        $this->assertCount(2, $gitIgnoreFile->getGitIgnoreRules());
    }
}
