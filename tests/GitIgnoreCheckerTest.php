<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests;

use Inmarelibero\GitIgnoreChecker\GitIgnoreChecker;

/**
 * Class GitIgnoreCheckerTest
 * @package Inmarelibero\GitIgnoreChecker\Tests
 *
 * @covers \Inmarelibero\GitIgnoreChecker\GitIgnoreChecker
 */
class GitIgnoreCheckerTest extends AbstractTestCase
{
    /**
     * @covers \Inmarelibero\GitIgnoreChecker\GitIgnoreChecker::isPathIgnored()
     *
     * @todo add assertions
     */
    public function testIsPathIgnored()
    {
        $gitIgnoreChecker = new GitIgnoreChecker(
            $this->getTestRepositoryPath()
        );

        $this->assertFalse(
            $gitIgnoreChecker->isPathIgnored('/foo/bar')
        );

        /**
         * @todo restore these assertions: the prloblem is that the two resources '/foo/ignore_me' and '/ignored_foo'
         * cannot be versioned and pushed in the repo, as they are correctly ignored by gut
         */
        //$this->assertTrue(
        //    $gitIgnoreChecker->isPathIgnored('/foo/ignore_me')
        //);
        //
        //$this->assertTrue(
        //    $gitIgnoreChecker->isPathIgnored('/ignored_foo')
        //);
    }
}
