<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests;

use Inmarelibero\GitIgnoreChecker\Model\RelativePath;
use Inmarelibero\GitIgnoreChecker\Utils\PathUtils;

/**
 * Unit tests for class PathUtils.
 */
class PathUtilsTest extends AbstractTestCase
{
    /**
     *
     */
    public function testGetDirectoriesToScan()
    {
        $testRepositoryPath = $this->getTestRepositoryPath();

        // '/'
        $this->assertCount(0, PathUtils::getRelativeDirectoriesToScan(new RelativePath($this->getTestRepository(), '/')));

        // '/foo'
        $this->assertCount(1, PathUtils::getRelativeDirectoriesToScan(new RelativePath($this->getTestRepository(), '/foo')));

        // '/foo/bar'
        $this->assertCount(2, PathUtils::getRelativeDirectoriesToScan(new RelativePath($this->getTestRepository(), '/foo/bar')));
    }
    
    /**
     * @return string
     */
    public function testMatchPath()
    {
        // test "target/": folder (due to the trailing /) recursively
        $this->doTestPathMatchesRule('/README', 'README/', false);
        $this->doTestPathMatchesRule('/foo/', 'foo/', true);
//        $this->doTestPathMatchesRule('/foo', 'foo/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/', 'bar_folder/', true);
//        $this->doTestPathMatchesRule('/foo/bar_folder', 'bar_folder/', false);

        // test "target": file or folder named target recursively
        $this->doTestPathMatchesRule('/README', 'README', true);
        $this->doTestPathMatchesRule('/foo', 'foo', true);
        $this->doTestPathMatchesRule('/foo/', 'foo', true);
        $this->doTestPathMatchesRule('/foo/bar_folder', 'bar_folder', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/', 'bar_folder', true);

        // test "/target": file or folder named target in the top-most directory (due to the leading /)
        $this->doTestPathMatchesRule('/README', '/README', true);
        $this->doTestPathMatchesRule('/foo', '/foo', true);
        $this->doTestPathMatchesRule('/foo/', '/foo', true);
        $this->doTestPathMatchesRule('/foo/bar_folder', '/foo', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/', '/foo', true);
        $this->doTestPathMatchesRule('/foo/bar_folder', '/bar_folder', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/', '/bar_folder', false);

        // test "/target/": folder named target in the top-most directory (leading and trailing /)
        $this->doTestPathMatchesRule('/README', '/README/', false);
        $this->doTestPathMatchesRule('/foo/', '/foo/', true);
//        $this->doTestPathMatchesRule('/foo', '/foo/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder', '/foo/', true);
        $this->doTestPathMatchesRule('/foo/bar_folder', '/bar_folder/', false);

        // test "*.class": every file or folder ending with .class recursively
        $this->doTestPathMatchesRule('/README', '/*.md', false);
        $this->doTestPathMatchesRule('/README.md', '/*.md', true);
        $this->doTestPathMatchesRule('/foo/README.md', '/*.md', false);
        $this->doTestPathMatchesRule('/README', '*.md', false);
        $this->doTestPathMatchesRule('/README.md', '*.md', true);
        $this->doTestPathMatchesRule('/foo/README.md', '*.md', true);

        $this->doTestPathMatchesRule('/.README', '/*.md', false);
        $this->doTestPathMatchesRule('/.README.md', '/*.md', true);
        $this->doTestPathMatchesRule('/foo/.README.md', '/*.md', false);
        $this->doTestPathMatchesRule('/.README', '*.md', false);
        $this->doTestPathMatchesRule('/.README.md', '*.md', true);
        $this->doTestPathMatchesRule('/foo/.README.md', '*.md', true);

        // test "#comment": nothing, this is a comment (first character is a #)
        // @todo restore: throws exception on __construct
//        $this->doTestPathMatchesRule(false, '/README', '# comment');
//        $this->doTestPathMatchesRule(false, '/foo', '# comment');
//        $this->doTestPathMatchesRule(false, '/foo/bar_folder', '# comment');

        // test "\#comment": every file or folder with name #comment (\ for escaping)
//        $this->doTestPathMatchesRule(true, '/#README', '\#README');
//        $this->doTestPathMatchesRule(false, '/foo', '\# comment');
//        $this->doTestPathMatchesRule(false, '/foo/bar_folder', '\# comment');

        // test "target/logs/": every folder named logs which is a subdirectory of a folder named target
        $this->doTestPathMatchesRule('/README', 'foo/bar_folder/', false);
        $this->doTestPathMatchesRule('/foo', 'foo/bar_folder/', false);

//        $this->doTestPathMatchesRule('/foo/bar_folder', 'foo/bar_folder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/', 'foo/bar_folder/', true);

        $this->doTestPathMatchesRule('/bar_folder/foo/', 'foo/bar_folder/', false);
        $this->doTestPathMatchesRule('/bar_folder/foo', 'foo/bar_folder/', false);

        $this->doTestPathMatchesRule('/foo/bar_folder/baz_folder', 'bar_folder/baz_folder/', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/baz_folder/', 'bar_folder/baz_folder/', true);

        $this->doTestPathMatchesRule('/README', '/foo/bar_folder/', false);
        $this->doTestPathMatchesRule('/foo', '/foo/bar_folder/', false);

//        $this->doTestPathMatchesRule('/foo/bar_folder', '/foo/bar_folder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/', '/foo/bar_folder/', true);

        $this->doTestPathMatchesRule('/bar_folder/foo/', '/foo/bar_folder/', false);
        $this->doTestPathMatchesRule('/bar_folder/foo', '/foo/bar_folder/', false);

        $this->doTestPathMatchesRule('/foo/bar_folder/baz_folder', '/bar_folder/baz_folder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/baz_folder/', '/bar_folder/baz_folder/', false);

        // test "target/*/logs/": every folder named logs two levels under a folder named target (* doesn’t include /)
        $this->doTestPathMatchesRule('/README', 'foo/*/bar_subfolder/', false);
        $this->doTestPathMatchesRule('/README', '/foo/*/bar_subfolder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/bar_subfolder/', 'foo/*/bar_subfolder/', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/bar_subfolder/', '/foo/*/bar_subfolder/', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/README', 'foo/*/bar_subfolder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/README', '/foo/*/bar_subfolder/', false);

        // test "target/**/logs/": every folder named logs somewhere under a folder named target (** includes /)
        $this->doTestPathMatchesRule('/README', 'foo/**/bar_subfolder/', false);
        $this->doTestPathMatchesRule('/README', '/foo/**/bar_subfolder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/bar_subfolder/', 'foo/**/bar_subfolder/', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/bar_subfolder/', '/foo/**/bar_subfolder/', true);
        $this->doTestPathMatchesRule('/foo/bar_folder/README', 'foo/**/bar_subfolder/', false);
        $this->doTestPathMatchesRule('/foo/bar_folder/README', '/foo/**/bar_subfolder/', false);
    }

    /**
     * @param $relativePath
     * @param $rule
     * @param $expectedMatch
     */
    private function doTestPathMatchesRule($relativePath, $rule, $expectedMatch)
    {
        $relativePath = new RelativePath($this->getTestRepository(), $relativePath);

        $this->assertEquals($expectedMatch, PathUtils::ruleMatchesPath($rule, $relativePath), $this->getErrorMessageForPathmatchesRule($expectedMatch, $rule, $relativePath));
    }

    /**
     * @param $expectedMatch
     * @param string $rule
     * @param $path
     * @return string
     */
    private function getErrorMessageForPathmatchesRule($expectedMatch, $rule, $path)
    {
        return sprintf(
            "Path \"%s\" %s have been matched against rule \"%s\".",
            $path,
            ($expectedMatch === true) ? 'should' : 'shouldn\'t',
            $rule
        );
    }
}
