<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests;

/**
 * Class GitIgnoreCheckerTest
 * @package Inmarelibero\GitIgnoreChecker\Tests
 */
class GitIgnoreCheckerTest extends AbstractTestCase
{
    public function testIsPathIgnored()
    {
        $this->assertFalse(
            $this->gitIgnoreChecker->isPathIgnored('/foo/bar')
        );

        $this->assertTrue(
            $this->gitIgnoreChecker->isPathIgnored('/foo/ignore_me')
        );

        $this->assertTrue(
            $this->gitIgnoreChecker->isPathIgnored('/ignored_foo')
        );
    }
}
