<?php

namespace Inmarelibero\GitIgnoreChecker\Model;

use Inmarelibero\GitIgnoreChecker\Exception\FileNotFoundException;
use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
use Inmarelibero\GitIgnoreChecker\Exception\LogicException;
use Inmarelibero\GitIgnoreChecker\Utils\PathUtils;

/**
 * Class RelativePath
 * @package Inmarelibero\GitIgnoreChecker\Model
 */
class RelativePath
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string the relative path, must begin with "/" because it's always relative to the Repository root
     */
    protected $path;

    /**
     * RelativePath constructor.
     *
     * @param Repository $repository
     * @param string $path path is absolute (must begin with ("/"), but always relative to Repository root
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function __construct(Repository $repository, $path)
    {
        $this->repository = $repository;
        $this->setPath($path);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string) $this->path;
    }

    /**
     * Set path
     *   - check if file/folder actually exists
     *
     * @param string $path
     * @return RelativePath
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function setPath(string $path): RelativePath
    {
        // check relative $path represents a valid file
        PathUtils::checkAbsolutePathIsValid(
            $this->repository->buildAbsolutePath($path)
        );

        $this->path = $path;

        return $this;
    }

    /**
     * Get Repository
     *
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get absolute path
     *
     * @return string
     */
    public function getAbsolutePath(): string
    {
        return $this->getRepository()->buildAbsolutePath($this->path);
    }

    /**
     * Return true if this represents a folder
     *
     * @return bool
     */
    public function isFolder() : bool
    {
        try {
            $this->checkIsFolder();
        } catch (LogicException $e) {
            return false;
        }

        return true;
    }

    /**
     * Throw an exception if this does not represent a folder
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function checkIsFolder() : void
    {
        PathUtils::checkAbsolutePathIsValid($this->getAbsolutePath(), true);
    }

    /**
     * Return true if this relative path contains a given path (eg. ".gitignore")
     *
     * @param string $path
     * @return bool
     * @throws LogicException
     */
    public function containsPath(string $path) : bool
    {
        $absolutePath = $this->getRepository()->buildAbsolutePath('/'.$this->getPath().'/'.$path);

        if (file_exists($absolutePath)) {
            if (!is_readable($absolutePath)) {
                throw new LogicException(
                    sprintf("Path \"%s\" has been found in folder \"%s\", but it's not readable..", $path, $this->getRepository()->getPath())
                );
            }

            return true;
        }

        return false;
    }
}
