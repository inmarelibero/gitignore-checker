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
     * @covers \Inmarelibero\GitIgnoreChecker\Model\GitIgnoreFile::buildFromRelativePathContainingGitIgnore()
     */
    public function testBuildFromRelativePathContainingGitIgnore()
    {
        $repository = $this->getTestRepository();

        $gitIgnoreFile = GitIgnoreFile::buildFromRelativePathContainingGitIgnore(new RelativePath($repository, '/'));
        $this->assertInstanceOf(GitIgnoreFile::class, $gitIgnoreFile);

        $this->assertCount(2, $gitIgnoreFile->getGitIgnoreRules());
    }

    /**
     * @covers \Inmarelibero\GitIgnoreChecker\Model\GitIgnoreFile::buildFromContent()
     */
    public function testBuildFromContent()
    {
        $repository = $this->getTestRepository();

        $gitIgnoreFile = GitIgnoreFile::buildFromContent(new RelativePath($repository, '/'), <<<EOF
foo
bar

baz
EOF
);
        $this->assertInstanceOf(GitIgnoreFile::class, $gitIgnoreFile);

        $this->assertCount(3, $gitIgnoreFile->getGitIgnoreRules());
    }
}
