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
        $this->doTestSingleRuleMatchesPath('README/', '/README', false);
        $this->doTestSingleRuleMatchesPath('foo/', '/foo/', true);
//        $this->doTestSingleRuleMatchesPath('foo/', '/foo', false);
        $this->doTestSingleRuleMatchesPath('bar_folder/', '/foo/bar_folder/', true);
//        $this->doTestSingleRuleMatchesPath('bar_folder/', '/foo/bar_folder', false);

        // test "target": file or folder named target recursively
        $this->doTestSingleRuleMatchesPath('README', '/README', true);
        $this->doTestSingleRuleMatchesPath('foo', '/foo', true);
        $this->doTestSingleRuleMatchesPath('foo', '/foo/', true);
        $this->doTestSingleRuleMatchesPath('bar_folder', '/foo/bar_folder', true);
        $this->doTestSingleRuleMatchesPath('bar_folder', '/foo/bar_folder/', true);

        // test "/target": file or folder named target in the top-most directory (due to the leading /)
        $this->doTestSingleRuleMatchesPath('/README', '/README', true);
        $this->doTestSingleRuleMatchesPath('/foo', '/foo', true);
        $this->doTestSingleRuleMatchesPath('/foo', '/foo/', true);
        $this->doTestSingleRuleMatchesPath('/foo', '/foo/bar_folder', true);
        $this->doTestSingleRuleMatchesPath('/foo', '/foo/bar_folder/', true);
        $this->doTestSingleRuleMatchesPath('/bar_folder', '/foo/bar_folder', false);
        $this->doTestSingleRuleMatchesPath('/bar_folder', '/foo/bar_folder/', false);

        // test "/target/": folder named target in the top-most directory (leading and trailing /)
        $this->doTestSingleRuleMatchesPath('/README/', '/README', false);
        $this->doTestSingleRuleMatchesPath('/foo/', '/foo/', true);
//        $this->doTestSingleRuleMatchesPath('/foo/', '/foo', false);
        $this->doTestSingleRuleMatchesPath('/foo/', '/foo/bar_folder', true);
        $this->doTestSingleRuleMatchesPath('/bar_folder/', '/foo/bar_folder', false);

        // test "*.class": every file or folder ending with .class recursively
        $this->doTestSingleRuleMatchesPath('/*.md', '/README', false);
        $this->doTestSingleRuleMatchesPath('/*.md', '/README.md', true);
        $this->doTestSingleRuleMatchesPath('/*.md', '/foo/README.md', false);
        $this->doTestSingleRuleMatchesPath('*.md', '/README', false);
        $this->doTestSingleRuleMatchesPath('*.md', '/README.md', true);
        $this->doTestSingleRuleMatchesPath('*.md', '/foo/README.md', true);

        $this->doTestSingleRuleMatchesPath('/*.md', '/.README', false);
        $this->doTestSingleRuleMatchesPath('/*.md', '/.README.md', true);
        $this->doTestSingleRuleMatchesPath('/*.md', '/foo/.README.md', false);
        $this->doTestSingleRuleMatchesPath('*.md', '/.README', false);
        $this->doTestSingleRuleMatchesPath('*.md', '/.README.md', true);
        $this->doTestSingleRuleMatchesPath('*.md', '/foo/.README.md', true);

        // test "#comment": nothing, this is a comment (first character is a #)
        // @todo restore: throws exception on __construct
//        $this->doTestSingleRuleMatchesPath(false, '/README', '# comment');
//        $this->doTestSingleRuleMatchesPath(false, '/foo', '# comment');
//        $this->doTestSingleRuleMatchesPath(false, '/foo/bar_folder', '# comment');

        // test "\#comment": every file or folder with name #comment (\ for escaping)
//        $this->doTestSingleRuleMatchesPath(true, '/#README', '\#README');
//        $this->doTestSingleRuleMatchesPath(false, '/foo', '\# comment');
//        $this->doTestSingleRuleMatchesPath(false, '/foo/bar_folder', '\# comment');

        // test "target/logs/": every folder named logs which is a subdirectory of a folder named target
        $this->doTestSingleRuleMatchesPath('foo/bar_folder/', '/README', false);
        $this->doTestSingleRuleMatchesPath('foo/bar_folder/', '/foo', false);

//        $this->doTestSingleRuleMatchesPath('foo/bar_folder/', '/foo/bar_folder', false);
        $this->doTestSingleRuleMatchesPath('foo/bar_folder/', '/foo/bar_folder/', true);

        $this->doTestSingleRuleMatchesPath('foo/bar_folder/', '/bar_folder/foo/', false);
        $this->doTestSingleRuleMatchesPath('foo/bar_folder/', '/bar_folder/foo', false);

        $this->doTestSingleRuleMatchesPath('bar_folder/baz_folder/', '/foo/bar_folder/baz_folder', true);
        $this->doTestSingleRuleMatchesPath('bar_folder/baz_folder/', '/foo/bar_folder/baz_folder/', true);

        $this->doTestSingleRuleMatchesPath('/foo/bar_folder/', '/README', false);
        $this->doTestSingleRuleMatchesPath('/foo/bar_folder/', '/foo', false);

//        $this->doTestSingleRuleMatchesPath('/foo/bar_folder/', '/foo/bar_folder', false);
        $this->doTestSingleRuleMatchesPath('/foo/bar_folder/', '/foo/bar_folder/', true);

        $this->doTestSingleRuleMatchesPath('/foo/bar_folder/', '/bar_folder/foo/', false);
        $this->doTestSingleRuleMatchesPath('/foo/bar_folder/', '/bar_folder/foo', false);

        $this->doTestSingleRuleMatchesPath('/bar_folder/baz_folder/', '/foo/bar_folder/baz_folder', false);
        $this->doTestSingleRuleMatchesPath('/bar_folder/baz_folder/', '/foo/bar_folder/baz_folder/', false);

        // test "target/*/logs/": every folder named logs two levels under a folder named target (* doesn’t include /)
        $this->doTestSingleRuleMatchesPath('foo/*/bar_subfolder/', '/README', false);
        $this->doTestSingleRuleMatchesPath('/foo/*/bar_subfolder/', '/README', false);
        $this->doTestSingleRuleMatchesPath('foo/*/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleRuleMatchesPath('/foo/*/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleRuleMatchesPath('foo/*/bar_subfolder/', '/foo/bar_folder/README', false);
        $this->doTestSingleRuleMatchesPath('/foo/*/bar_subfolder/', '/foo/bar_folder/README', false);

        // test "target/**/logs/": every folder named logs somewhere under a folder named target (** includes /)
        $this->doTestSingleRuleMatchesPath('foo/**/bar_subfolder/', '/README', false);
        $this->doTestSingleRuleMatchesPath('/foo/**/bar_subfolder/', '/README', false);
        $this->doTestSingleRuleMatchesPath('foo/**/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleRuleMatchesPath('/foo/**/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleRuleMatchesPath('foo/**/bar_subfolder/', '/foo/bar_folder/README', false);
        $this->doTestSingleRuleMatchesPath('/foo/**/bar_subfolder/', '/foo/bar_folder/README', false);
    }

    /**
     * @param string $rule
     * @param string $relativePath
     * @param bool $expectedMatch
     */
    private function doTestSingleRuleMatchesPath(string $rule, string $relativePath, bool $expectedMatch) : void
    {
        $relativePath = new RelativePath($this->getTestRepository(), $relativePath);

        $this->assertEquals($expectedMatch, PathUtils::ruleMatchesPath($rule, $relativePath), $this->getErrorMessageForPathmatchesRule($expectedMatch, $rule, $relativePath));

        // automatically test $rule adding an initial "!": $relative must be matched anyway
        if (strpos($rule, "!") !== 0) {
            $ruleWithInitialExclamationMark = '!'.$rule;

            $this->assertEquals($expectedMatch, PathUtils::ruleMatchesPath($ruleWithInitialExclamationMark, $relativePath), $this->getErrorMessageForPathmatchesRule($expectedMatch, $ruleWithInitialExclamationMark, $relativePath));
        }
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
