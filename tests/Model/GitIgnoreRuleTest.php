<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests\Model;

use Inmarelibero\GitIgnoreChecker\Model\GitIgnoreFile;
use Inmarelibero\GitIgnoreChecker\Model\GitIgnoreRule;
use Inmarelibero\GitIgnoreChecker\Model\RelativePath;
use Inmarelibero\GitIgnoreChecker\Tests\AbstractTestCase;

/**
 * Class GitIgnoreRuleTest
 * @package Inmarelibero\GitIgnoreChecker\Tests\Model
 */
class GitIgnoreRuleTest extends AbstractTestCase
{
    /**
     * @return string
     */
    public function testMatchPath()
    {
        // test "target/": folder (due to the trailing /) recursively
        $this->doTestGitIgnoreRule(false, '/README', 'README/');
        $this->doTestGitIgnoreRule(true, '/foo', 'foo/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'bar_folder/');

        // test "target": file or folder named target recursively
        $this->doTestGitIgnoreRule(true, '/README', 'README');
        $this->doTestGitIgnoreRule(true, '/foo', 'foo');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'bar_folder');

        // test "/target": file or folder named target in the top-most directory (due to the leading /)
        $this->doTestGitIgnoreRule(true, '/README', '/README');
        $this->doTestGitIgnoreRule(true, '/foo', '/foo');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', '/foo');
        $this->doTestGitIgnoreRule(false, '/foo/bar_folder', '/bar_folder');

        // test "/target/": folder named target in the top-most directory (leading and trailing /)
        $this->doTestGitIgnoreRule(false, '/README', '/README/');
        $this->doTestGitIgnoreRule(true, '/foo', '/foo/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', '/foo/');
        $this->doTestGitIgnoreRule(false, '/foo/bar_folder', '/bar_folder/');

        // test "*.class": every file or folder ending with .class recursively
        $this->doTestGitIgnoreRule(false, '/README', '*.md');
        $this->doTestGitIgnoreRule(true, '/README.md', '*.md');
        $this->doTestGitIgnoreRule(true, '/foo/README.md', '*.md');
        $this->doTestGitIgnoreRule(false, '/README', '/*.md');
        $this->doTestGitIgnoreRule(true, '/README.md', '/*.md');
        $this->doTestGitIgnoreRule(false, '/foo/README.md', '/*.md');
        $this->doTestGitIgnoreRule(false, '/.README', '/*.md');
        $this->doTestGitIgnoreRule(true, '/.README.md', '/*.md');
        $this->doTestGitIgnoreRule(false, '/foo/.README.md', '/*.md');

        // test "#comment": nothing, this is a comment (first character is a #)
        // @todo restore: throws exception on __construct
//        $this->doTestGitIgnoreRule(false, '/README', '# comment');
//        $this->doTestGitIgnoreRule(false, '/foo', '# comment');
//        $this->doTestGitIgnoreRule(false, '/foo/bar_folder', '# comment');

        // test "\#comment": every file or folder with name #comment (\ for escaping)
//        $this->doTestGitIgnoreRule(true, '/#README', '\#README');
//        $this->doTestGitIgnoreRule(false, '/foo', '\# comment');
//        $this->doTestGitIgnoreRule(false, '/foo/bar_folder', '\# comment');

        // test "target/logs/": every folder named logs which is a subdirectory of a folder named target
        $this->doTestGitIgnoreRule(false, '/README', 'foo/bar_folder/');
        $this->doTestGitIgnoreRule(false, '/foo', 'foo/bar_folder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'foo/bar_folder/');

        // test "target/*/logs/": every folder named logs two levels under a folder named target (* doesn’t include /)
        $this->doTestGitIgnoreRule(false, '/README', 'foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(false, '/README', '/foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder/bar_subfolder/', 'foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder/bar_subfolder/', '/foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(false, '/foo/bar_folder/README', 'foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(false, '/foo/bar_folder/README', '/foo/*/bar_subfolder/');

        // test "target/**/logs/": every folder named logs somewhere under a folder named target (** includes /)
        $this->doTestGitIgnoreRule(false, '/README', 'foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(false, '/README', '/foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder/bar_subfolder/', 'foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder/bar_subfolder/', '/foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(false, '/foo/bar_folder/README', 'foo/*/bar_subfolder/');
        $this->doTestGitIgnoreRule(false, '/foo/bar_folder/README', '/foo/*/bar_subfolder/');





        // test files in root directory
        $this->doTestGitIgnoreRule(true, '/README', 'readme');
        $this->doTestGitIgnoreRule(false, '/README', 'readme/');

        $this->doTestGitIgnoreRule(true, '/.README', '.readme');
        $this->doTestGitIgnoreRule(false, '/.README', '.readme/');

        // test files in subfolder of root directory
        $this->doTestGitIgnoreRule(false, '/foo/bar', 'readme');
        $this->doTestGitIgnoreRule(true, '/foo/bar', 'foo/bar');
        $this->doTestGitIgnoreRule(false, '/foo/bar', 'foo/bar/');

        $this->doTestGitIgnoreRule(false, '/.foo/bar', 'readme');
        $this->doTestGitIgnoreRule(true, '/.foo/bar', '.foo/bar');
        $this->doTestGitIgnoreRule(false, '/.foo/bar', '.foo/bar/');

        // test directory in root directory
        $this->doTestGitIgnoreRule(true, '/foo', 'foo');
        $this->doTestGitIgnoreRule(true, '/foo/', 'foo/');

        $this->doTestGitIgnoreRule(true, '/.foo', '.foo');
        $this->doTestGitIgnoreRule(true, '/.foo', '.foo/');

        // test directory in subfolder of root directory
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'foo');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'foo/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'foo');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'foo/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'bar_folder');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'bar_folder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', 'foo/bar_folder');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder/', 'foo/bar_folder/');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder', '/foo/bar_folder');
        $this->doTestGitIgnoreRule(true, '/foo/bar_folder/', '/foo/bar_folder/');

        $this->doTestGitIgnoreRule(true, '/.foo/bar_folder', '.foo/bar_folder');
        $this->doTestGitIgnoreRule(true, '/.foo/bar_folder', '.foo/bar_folder/');

        // test regexes
        $this->doTestGitIgnoreRule(true, '/README', '*');
        $this->doTestGitIgnoreRule(true, '/README', 'readme*');
        $this->doTestGitIgnoreRule(true, '/README', 're*');
        $this->doTestGitIgnoreRule(true, '/README', '*readme');
        $this->doTestGitIgnoreRule(true, '/README', '*me');

        $this->doTestGitIgnoreRule(true, '/.README', '*');
        $this->doTestGitIgnoreRule(true, '/.README', '.*');
        $this->doTestGitIgnoreRule(true, '/.README', '.readme*');
        $this->doTestGitIgnoreRule(false, '/.README', 'readme*');
        $this->doTestGitIgnoreRule(false, '/.README', 're*');
        $this->doTestGitIgnoreRule(true, '/.README', '.re*');
        $this->doTestGitIgnoreRule(true, '/.README', '.*readme');
        $this->doTestGitIgnoreRule(true, '/.README', '.*me');

        $this->doTestGitIgnoreRule(true, '/foo/bar', 'foo*');
        $this->doTestGitIgnoreRule(true, '/foo/bar', 'bar*');
    }

    /**
     * Test automatically:
     *  - $rule
     *  - $rule with all uppercase characters
     *  - $rule with all lowercase characters
     *
     * @param bool $expectedMatch
     * @param string $relativePath eg. "/foo"
     * @param string $rule
     */
    private function doTestGitIgnoreRule($expectedMatch, $relativePath, $rule)
    {
        $this->doTestSingle($expectedMatch, $relativePath, $rule);
    }

    /**
     * @param $expectedMatch
     * @param $relativePath
     * @param $rule
     */
    private function doTestSingle($expectedMatch, $relativePath, $rule)
    {
        if (!is_bool($expectedMatch)) {
            throw new \InvalidArgumentException("ExpectedMatch must be a boolean.");
        }

        $gitIgnoreRule = new GitIgnoreRule(
            new GitIgnoreFile(new RelativePath($this->getTestRepository(), '/')),
            $rule
        );

        $relativePath = new RelativePath($this->getTestRepository(), $relativePath);

        $this->assertEquals(
            $expectedMatch,
            $gitIgnoreRule->getRuleDecisionOnPath($relativePath),
            $this->getErrorMessageForMatchPath($expectedMatch, $gitIgnoreRule, $relativePath)
        );
    }

    /**
     * @param $expectedMatch
     * @param GitIgnoreRule $gitIgnoreRule
     * @param $path
     * @return string
     */
    private function getErrorMessageForMatchPath($expectedMatch, GitIgnoreRule $gitIgnoreRule, $path)
    {
        return sprintf(
            "Path \"%s\" %s have been matched against rule \"%s\".",
            $path,
            ($expectedMatch === true) ? 'should' : 'shouldn\'t',
            $gitIgnoreRule->getRule()
        );
    }
}
