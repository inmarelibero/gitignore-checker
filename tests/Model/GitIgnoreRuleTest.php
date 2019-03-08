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
     *
     */
    public function testGetRuleDecisionOnPath()
    {
        // test "target/": folder (due to the trailing /) recursively
        $this->doTestSingleGetRuleDecisionOnPath('README/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('foo/', '/foo', true);
        $this->doTestSingleGetRuleDecisionOnPath('bar_folder/', '/foo/bar_folder', true);

        // test "target": file or folder named target recursively
        $this->doTestSingleGetRuleDecisionOnPath('README', '/README', true);
        $this->doTestSingleGetRuleDecisionOnPath('foo', '/foo', true);
        $this->doTestSingleGetRuleDecisionOnPath('bar_folder', '/foo/bar_folder', true);

        // test "/target": file or folder named target in the top-most directory (due to the leading /)
        $this->doTestSingleGetRuleDecisionOnPath('/README', '/README', true);
        $this->doTestSingleGetRuleDecisionOnPath('/foo', '/foo', true);
        $this->doTestSingleGetRuleDecisionOnPath('/foo', '/foo/bar_folder', true);
        $this->doTestSingleGetRuleDecisionOnPath('/bar_folder', '/foo/bar_folder', false);

        // test "/target/": folder named target in the top-most directory (leading and trailing /)
        $this->doTestSingleGetRuleDecisionOnPath('/README/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/', '/foo', true);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/', '/foo/bar_folder', true);
        $this->doTestSingleGetRuleDecisionOnPath('/bar_folder/', '/foo/bar_folder', false);

        // test "*.class": every file or folder ending with .class recursively
        $this->doTestSingleGetRuleDecisionOnPath('*.md', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('*.md', '/README.md', true);
        $this->doTestSingleGetRuleDecisionOnPath('*.md', '/foo/README.md', true);
        $this->doTestSingleGetRuleDecisionOnPath('/*.md', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/*.md', '/README.md', true);
        $this->doTestSingleGetRuleDecisionOnPath('/*.md', '/foo/README.md', false);
        $this->doTestSingleGetRuleDecisionOnPath('/*.md', '/.README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/*.md', '/.README.md', true);
        $this->doTestSingleGetRuleDecisionOnPath('/*.md', '/foo/.README.md', false);

        // test "#comment": nothing, this is a comment (first character is a #)
        // @todo restore: throws exception on __construct
//        $this->doTestSingleGetRuleDecisionOnPath(false, '/README', '# comment');
//        $this->doTestSingleGetRuleDecisionOnPath(false, '/foo', '# comment');
//        $this->doTestSingleGetRuleDecisionOnPath(false, '/foo/bar_folder', '# comment');

        // test "\#comment": every file or folder with name #comment (\ for escaping)
//        $this->doTestSingleGetRuleDecisionOnPath(true, '/#README', '\#README');
//        $this->doTestSingleGetRuleDecisionOnPath(false, '/foo', '\# comment');
//        $this->doTestSingleGetRuleDecisionOnPath(false, '/foo/bar_folder', '\# comment');

        // test "target/logs/": every folder named logs which is a subdirectory of a folder named target
        $this->doTestSingleGetRuleDecisionOnPath('foo/bar_folder/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('foo/bar_folder/', '/foo', false);
        $this->doTestSingleGetRuleDecisionOnPath('foo/bar_folder/', '/foo/bar_folder', true);

        // test "target/*/logs/": every folder named logs two levels under a folder named target (* doesn’t include /)
        $this->doTestSingleGetRuleDecisionOnPath('foo/*/bar_subfolder/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/*/bar_subfolder/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('foo/*/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/*/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleGetRuleDecisionOnPath('foo/*/bar_subfolder/', '/foo/bar_folder/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/*/bar_subfolder/', '/foo/bar_folder/README', false);

        // test "target/**/logs/": every folder named logs somewhere under a folder named target (** includes /)
        $this->doTestSingleGetRuleDecisionOnPath('foo/*/bar_subfolder/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/*/bar_subfolder/', '/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('foo/*/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/*/bar_subfolder/', '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleGetRuleDecisionOnPath('foo/*/bar_subfolder/', '/foo/bar_folder/README', false);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/*/bar_subfolder/', '/foo/bar_folder/README', false);
    }

    /**
     * @param string $relativePath
     * @param string $rule
     * @param bool$expectedMatch
     */
    private function doTestSingleGetRuleDecisionOnPath(string $rule, string $relativePath, bool $expectedMatch) : void
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
