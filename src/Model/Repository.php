<?php

namespace Inmarelibero\GitIgnoreChecker\Model;

use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
use Inmarelibero\GitIgnoreChecker\Exception\LogicException;
use Inmarelibero\GitIgnoreChecker\Utils\PathUtils;
use Inmarelibero\GitIgnoreChecker\Utils\StringUtils;

/**
 * Class Repository
 * @package Inmarelibero\GitIgnoreChecker\Model
 */
class Repository
{
    /**
     * @var string project root dir
     */
    protected $path;

    /**
     * Repository constructor.
     *
     * @param string $path absolute path of the repository
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function __construct($path)
    {
        $this->setPath($path);
    }

    /**
     * Set the path of the Git repository (project root)
     *
     * @param $path
     * @return Repository
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function setPath($path) : Repository
    {
        PathUtils::checkAbsolutePathIsFolder($path);

        $this->path = realpath($path);

        return $this;
    }

    /**
     * Get the absolute path of the Repository project root
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Build the absolute path for a given relative path
     *
     * @param string $relativePath
     * @return string
     * @throws InvalidArgumentException
     */
    public function buildAbsolutePath(string $relativePath) : string
    {
        if (!StringUtils::stringHasInitialSlash($relativePath)) {
            throw new InvalidArgumentException(sprintf("Path to check must begin with \"\/\", \"%s\" given.", $relativePath));
        }

        $output = sprintf("%s/%s", $this->getPath(), $relativePath);
        $output = PathUtils::removeDoubleSlashes($output);

        return $output;
    }
}
