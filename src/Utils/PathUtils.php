<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Utils;

use Inmarelibero\GitIgnoreChecker\Exception\InvalidArgumentException;
use Inmarelibero\GitIgnoreChecker\Exception\LogicException;
use Inmarelibero\GitIgnoreChecker\Model\RelativePath;

/**
 * Class PathUtils
 * @package Inmarelibero\GitIgnoreChecker\Utils
 */
class PathUtils
{
    /**
     * Return true if $rule matches $relativePath
     *
     * @param string $rule
     * @param string $relativePath
     * @return bool
     */
    public static function ruleMatchesPath($rule, RelativePath $relativePath) : bool
    {
        $pathToMatch = $relativePath->getPath();

        if ($relativePath->isFolder()) {
            $pathToMatch = StringUtils::addTrailingSlashIfMissing($pathToMatch);
        }

        if (StringUtils::ruleIsOnSubfolders($rule)) {
            return StringUtils::ruleComplexMatchesPath($rule, $pathToMatch) === true;
        }

        if (StringUtils::ruleSimpleMatchesPath($rule, $pathToMatch)) {
            return true;
        }

        return false;
    }

    /**
     * Array containing all the paths to be scanned, contained in the $repositoryBaseDir (the first one coincides with it)
     *
     * @param RelativePath $relativePath
     * @return string[]
     */
    public static function getRelativeDirectoriesToScan(RelativePath $relativePath) : array
    {
        $output = [];

        $tokens = StringUtils::explodeStringWithDirectorySeparatorAsDelimiter($relativePath->getPath());
        $tokensCount = \count($tokens);

        for ($i = 0; $i < $tokensCount; $i++) {
            $output[] = "/" . implode('/', array_slice($tokens, 0, $i));
        }

        return $output;
    }

    /**
     * Throws exception if $path does not represent a readable and existing folder
     *
     * @param string $path
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public static function checkAbsolutePathIsFolder(string $path) : void
    {
        self::checkAbsolutePathIsValid($path, true);
    }

    /**
     * Throws exception if $path does not represent a readable and existing file/folder
     *
     * @param string $path
     * @param bool $checkIsDir
     * @param bool $checkIsFile
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public static function checkAbsolutePathIsValid(string $path, $checkIsDir = false, $checkIsFile = false) : void
    {
        if (!StringUtils::stringHasInitialSlash($path)) {
            throw new LogicException(sprintf("Argument must be an absolute path: \"%s\" given.", $path));
        }

        if (!file_exists($path)) {
            throw new LogicException(sprintf("Path \"%s\" does not exist.", $path));
        }

        if ($checkIsDir === true) {
            if (!is_dir($path)) {
                throw new LogicException(sprintf("Path \"%s\" exists but it's not a directory.", $path));
            }
        }

        if ($checkIsFile === true) {
            if (!is_file($path)) {
                throw new LogicException(sprintf("Path \"%s\" exists but it's not a file.", $path));
            }
        }

        if (!is_readable($path)) {
            throw new LogicException(sprintf("Path \"%s\" exists but it's not readable.", $path));
        }

        if (realpath($path) === false) {
            throw new LogicException(sprintf("Path \"%s\" does not exist.", $path));
        }
    }

    /**
     * Replaces "//" with "/"
     *
     * @param string $input
     * @return string
     */
    public static function removeDoubleSlashes(string $input) : string
    {
        return preg_replace("#\/\/#", "/", $input);  //@todo handle "#" in $input
    }
}
