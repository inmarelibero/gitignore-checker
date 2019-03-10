<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests\Model;

use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
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
//        $this->doTestSingleIsPathIgnored(false, '/README', '# comment');
//        $this->doTestSingleIsPathIgnored(false, '/foo', '# comment');
//        $this->doTestSingleIsPathIgnored(false, '/foo/bar_folder', '# comment');

        // test "\#comment": every file or folder with name #comment (\ for escaping)
        $this->doTestSingleGetRuleDecisionOnPath('\#README', '/#README', true);
        $this->doTestSingleGetRuleDecisionOnPath('\#README', '/#README', true);
        $this->doTestSingleGetRuleDecisionOnPath('\#foo', '/#foo', true);
        $this->doTestSingleGetRuleDecisionOnPath('\#foo/', '/#foo', true);
        $this->doTestSingleGetRuleDecisionOnPath('/foo/\#README', '/foo/#README', true);

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
     *
     */
    public function testConstructWithComment()
    {
        foreach ([
            '#comment',
            '# comment',
            ' # comment',
         ] as $rule) {
            try {
                new GitIgnoreRule(
                    GitIgnoreFile::buildFromRelativePathContainingGitIgnore(new RelativePath($this->getTestRepository(), '/')),
                    $rule,
                    0
                );
                $this->fail(sprintf(
                    "Object GitIgnoreRule shouldn't have been created with rule = \"%s\".", $rule
                ));
            } catch (InvalidArgumentException $e) {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @param string $rule
     * @param string $relativePath
     * @param bool$expectedMatch
     */
    private function doTestSingleGetRuleDecisionOnPath(string $rule, string $relativePath, bool $expectedMatch) : void
    {
        if (!is_bool($expectedMatch)) {
            throw new \InvalidArgumentException("ExpectedMatch must be a boolean.");
        }

        $gitIgnoreRule = new GitIgnoreRule(
            GitIgnoreFile::buildFromRelativePathContainingGitIgnore(new RelativePath($this->getTestRepository(), '/')),
            $rule,
            0
        );

        $relativePath = new RelativePath($this->getTestRepository(), $relativePath);

        $this->assertEquals(
            $expectedMatch,
            $gitIgnoreRule->getRuleDecisionOnPath($relativePath),
            $this->getErrorMessageForMatchPath($expectedMatch, $gitIgnoreRule, $relativePath)
        );

        // automatically test $rule adding an initial "!": must always not ignore the file
        if (strpos($rule, "!") !== 0) {
            $ruleWithInitialExclamationMark = '!'.$rule;

            $this->doTestSingleGetRuleDecisionOnPath($ruleWithInitialExclamationMark, $relativePath->getPath(), false);
        }
    }

    /**
     * @param bool $expectedMatch
     * @param GitIgnoreRule $gitIgnoreRule
     * @param RelativePath $relativePath
     * @return string
     */
    private function getErrorMessageForMatchPath(bool $expectedMatch, GitIgnoreRule $gitIgnoreRule, RelativePath $relativePath)
    {
        return sprintf(
            "Path \"%s\" %s have been matched against rule \"%s\".",
            $relativePath->getPath(),
            ($expectedMatch === true) ? 'should' : 'shouldn\'t',
            $gitIgnoreRule->getRule()
        );
    }
}
