<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Model;

use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
use Inmarelibero\GitIgnoreChecker\Utils\PathUtils;

/**
 * Class GitIgnoreRule
 * @package Inmarelibero\GitIgnoreChecker\Model
 *
 * @see https://git-scm.com/docs/gitignore
 *
 * Represent a .gitignore rule
 */
class GitIgnoreRule
{
    /**
     * @var GitIgnoreFile
     */
    protected $gitIgnoreFile;

    /**
     * @var string represents a line of a .gitignre file
     */
    protected $rule;

    /**
     * GitIgnoreRule constructor.
     * @param GitIgnoreFile $gitIgnoreFile
     * @param string $rule
     * @throws InvalidArgumentException
     */
    public function __construct(GitIgnoreFile $gitIgnoreFile, string $rule)
    {
        $this->gitIgnoreFile = $gitIgnoreFile;
        $this->rule = $this->parseRule($rule);
    }

    /**
     * @param string $rule
     * @return string
     * @throws InvalidArgumentException
     */
    private function parseRule(string $rule) : string
    {
        $rule = trim($rule);

        if (empty($rule)) {
            throw new InvalidArgumentException(
                sprintf("Rule must be a valid string, \"%s\" given.", $rule)
            );
        }

        // rule begins with a comment
        if (strpos($rule, '#') === 0) {
            throw new InvalidArgumentException(
                sprintf("Rule cannot be created from comment, \"%s\" given.", $rule)
            );
        }

        return $rule;
    }

    /**
     * Get original rule
     *
     * @return string
     */
    public function getRule() : string
    {
        return $this->rule;
    }

    /**
     * If $rule (eg. "foo") is involved in $pathRelativeToRootDir (path is matched), return:
     *  - true if $pathRelativeToRootDir must be ignored
     *  - false if $pathRelativeToRootDir must not be ignored
     *  - null if $pathRelativeToRootDir is not involved in decision
     *
     * @param RelativePath $relativePath
     * @return bool|null
     */
    public function getRuleDecisionOnPath(RelativePath $relativePath)
    {
        // @todo handle return null

        if (PathUtils::ruleMatchesPath($this->getRule(), $relativePath)) {
            return true;
        }

        return false;
    }
}
