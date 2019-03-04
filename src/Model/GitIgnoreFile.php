<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Model;

use Inmarelibero\GitIgnoreChecker\Exception\FileNotFoundException;
use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
use Inmarelibero\GitIgnoreChecker\Exception\LogicException;
use Inmarelibero\GitIgnoreChecker\Exception\RuleNotFoundException;
use Inmarelibero\GitIgnoreChecker\Utils\PathUtils;

/**
 * Class GitIgnoreFile
 * @package Inmarelibero\GitIgnoreChecker\Model
 *
 * @see https://git-scm.com/docs/gitignore
 *
 * Represent a .gitignore file
 */
class GitIgnoreFile
{
    /**
     * @var RelativePath
     */
    protected $relativePath;

    /**
     * @var GitIgnoreRule[]
     */
    protected $gitIgnoreRules = [];

    /**
     * GitIgnoreFile constructor.
     *
     * @param RelativePath $relativePathContainingGitIgnore path containing a .gitignore file, eg. "/", "/foo/", ""/foo/bar/""
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function __construct(RelativePath $relativePathContainingGitIgnore)
    {
        $this->setRelativePath($relativePathContainingGitIgnore);
        $this->parseContent();
    }

    /**
     * Set relative path containing the .gitignore file
     *
     * @param RelativePath $relativePathContainingGitIgnore
     * @return GitIgnoreFile
     * @throws InvalidArgumentException
     * @throws FileNotFoundException
     * @throws LogicException
     */
    private function setRelativePath(RelativePath $relativePathContainingGitIgnore) : GitIgnoreFile
    {
        $relativePathContainingGitIgnore->checkIsFolder();


        if (!$relativePathContainingGitIgnore->containsPath('/.gitignore')) {
            throw new FileNotFoundException(
                sprintf("The path \"%s\" does not contain a .gitignore file.", $relativePathContainingGitIgnore->getPath())
            );
        }

        /*
         * check that $path represents the path containing the .gitignore file, not the path containing the ".gitignore" string
         */
        if (preg_match("#\.gitignore$#", $relativePathContainingGitIgnore->getPath())) {
            throw new InvalidArgumentException(
                sprintf("The path must not end with .gitignore: \"%s\" given.", $relativePathContainingGitIgnore->getPath())
            );
        }

        $this->relativePath = $relativePathContainingGitIgnore;

        return $this;
    }

    /**
     * Return the absolute path for the .gitignore file
     *
     * @param RelativePath $relativePathContainingGitIgnore
     * @return string
     */
    private function buildAbsolutePathForGitIgnore() : string
    {
        return sprintf("%s/.gitignore", $this->relativePath->getAbsolutePath());
    }

    /**
     * Get RelativePath
     *
     * @return RelativePath
     */
    public function getRelativePath() : RelativePath
    {
        return $this->relativePath;
    }

    /**
     * Parse every line of the .gitignore content
     *
     * @throws InvalidArgumentException
     */
    private function parseContent() : void
    {
        $absolutePath = $this->buildAbsolutePathForGitIgnore();

        $lines = file($absolutePath, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            $this->gitIgnoreRules[] = new GitIgnoreRule($this, $line);
        }
    }

    /**
     * Return the GitIgnore parsed rules
     *
     * @return GitIgnoreRule[]
     */
    public function getGitIgnoreRules() : array
    {
        return $this->gitIgnoreRules;
    }

    /**
     * Return true if a given $path is ignored by file .gitignore
     *
     * @param RelativePath $relativepath
     * @return bool
     */
    public function isPathIgnored(RelativePath $relativepath) : bool
    {
        /** @var GitIgnoreRule $gitIgnoreRule */
        try {
            $gitIgnoreRule = $this->getLastGitIgnoreRuleInvolvedInPath($relativepath);
        } catch (RuleNotFoundException $e) {
            return false;
        }

        if ($gitIgnoreRule->getRuleDecisionOnPath($relativepath) === true) {
            return true;
        }

        return false;
    }

    /**
     * Get the last GitIgnoreRule that matches a given path
     * Rule will be applied and the decision to ignore or not the path will be taken
     *
     * @param $relativePath
     * @return GitIgnoreRule
     */
    private function getLastGitIgnoreRuleInvolvedInPath(RelativePath $relativePath) : GitIgnoreRule
    {
        /** @var GitIgnoreRule[] $reversedGitIgnoreRules */
        $reversedGitIgnoreRules = array_reverse($this->getGitIgnoreRules());

        foreach ($reversedGitIgnoreRules as $gitIgnoreRule) {
            if ($gitIgnoreRule->getRuleDecisionOnPath($relativePath) === true) {
                return $gitIgnoreRule;
            }
        }

        throw new RuleNotFoundException();
    }
}
