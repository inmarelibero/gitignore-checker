<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker;

use Inmarelibero\GitIgnoreChecker\Exception\FileNotFoundException;
use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
use Inmarelibero\GitIgnoreChecker\Exception\LogicException;
use Inmarelibero\GitIgnoreChecker\Model\GitIgnore\File;
use Inmarelibero\GitIgnoreChecker\Model\RelativePath;
use Inmarelibero\GitIgnoreChecker\Model\Repository;
use Inmarelibero\GitIgnoreChecker\Utils\PathUtils;

/**
 * Class GitIgnoreChecker
 * @package Inmarelibero\GitIgnoreChecker
 */
final class GitIgnoreChecker
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Filename to look for containing the gitignore rules.
     *
     * This is `.gitignore` for Git repositories, but other libraries may use the same syntax for similar purposes.
     * @see https://github.com/wp-cli/dist-archive-command/
     *
     * @var string
     */
    protected $gitignoreFilename = '.gitignore';

    /**
     * GitIgnoreChecker constructor.
     *
     * @param string $repositoryPath absolute path representing the Repository project root
     * @param string $gitignoreFilename The filename for the text file containing the rules.
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function __construct($repositoryPath, string $gitignoreFilename = '.gitignore')
    {
        $this->repository = new Repository($repositoryPath);
        $this->gitignoreFilename  = $gitignoreFilename;
    }

    /**
     * @return Repository
     */
    public function getRepository() : Repository
    {
        return $this->repository;
    }

    /**
     * Return true if a given path is ignored
     *
     * $path must begin with "/" but it's always relative to Repository root
     *
     * @param string $path
     * @return bool
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function isPathIgnored($path) : bool
    {
        $relativePathToCheck = new RelativePath($this->getRepository(), $path);

        // for each parent directory, read possible .gitignore and check if $path is ignored by it
        $directories = PathUtils::getRelativeDirectoriesToScan($relativePathToCheck);

        // @todo check order priority
        foreach ($directories as $directory) {
            $relativePathToScan = new RelativePath($this->getRepository(), $directory);

            try {
                $file = $this->searchGitIgnoreFileInRelativePath($relativePathToScan);
            } catch (FileNotFoundException $e) {
                continue;
            }

            if (!$file instanceof File) {
                continue;
            }

            if ($file->isPathIgnored($relativePathToCheck)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $relativePath
     * @return File
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function searchGitIgnoreFileInRelativePath(RelativePath $relativePath) : File
    {
        return File::buildFromRelativePathContainingGitIgnore($relativePath, $this->gitignoreFilename);
    }
}
