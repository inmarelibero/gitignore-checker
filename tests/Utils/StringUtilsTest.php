<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests;

use Inmarelibero\GitIgnoreChecker\Utils\StringUtils;

/**
 * Unit tests for class StringUtils
 */
class StringUtilsTest extends AbstractTestCase
{
    /**
     *
     */
    public function testStringHasInitialSlash()
    {
        $this->assertTrue(StringUtils::stringHasInitialSlash('/'));
        $this->assertTrue(StringUtils::stringHasInitialSlash('/foo'));
        $this->assertTrue(StringUtils::stringHasInitialSlash('/foo/bar'));
        $this->assertTrue(StringUtils::stringHasInitialSlash('/foo/.gitignore'));
        $this->assertTrue(StringUtils::stringHasInitialSlash('/.gitignore'));

        $this->assertFalse(StringUtils::stringHasInitialSlash(''));
        $this->assertFalse(StringUtils::stringHasInitialSlash('foo'));
        $this->assertFalse(StringUtils::stringHasInitialSlash('foo/bar'));
        $this->assertFalse(StringUtils::stringHasInitialSlash('foo/.gitignore'));
        $this->assertFalse(StringUtils::stringHasInitialSlash('.gitignore'));
    }

    /**
     *
     */
    public function testExplodeStringWithDirectorySeparatorAsDelimiter()
    {
        $this->assertEquals(
            [],
            StringUtils::explodeStringWithDirectorySeparatorAsDelimiter('')
        );

        $this->assertEquals(
            [],
            StringUtils::explodeStringWithDirectorySeparatorAsDelimiter('/')
        );

        $this->assertEquals(
            ['foo'],
            StringUtils::explodeStringWithDirectorySeparatorAsDelimiter('/foo')
        );

        $this->assertEquals(
            ['foo', 'bar'],
            StringUtils::explodeStringWithDirectorySeparatorAsDelimiter('/foo/bar')
        );

        $this->assertEquals(
            ['foo', 'bar'],
            StringUtils::explodeStringWithDirectorySeparatorAsDelimiter('foo/bar')
        );

        $this->assertEquals(
            ['foo', 'bar'],
            StringUtils::explodeStringWithDirectorySeparatorAsDelimiter('/foo/bar')
        );
    }
}
