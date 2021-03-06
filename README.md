**GitignoreChecker** is a PHP library to check if a given paths is ignored by GIT.

USAGE
===

Initialize an object, providing the GIT repository root folder:

    $gitIgnoreChecker = new GitIgnoreChecker(__DIR__);

You can now easily check if a given path is ignored by some `.gitignore` rule, with:

     $gitIgnoreChecker->isPathIgnored('/foo');  // true|false
     $gitIgnoreChecker->isPathIgnored('/README');
     $gitIgnoreChecker->isPathIgnored('/foo/bar');
     $gitIgnoreChecker->isPathIgnored('/foo/bar/baz');
     $gitIgnoreChecker->isPathIgnored('/.foo');
     ...

RUN TESTS
===

After making sure you installed dependencies with command `composer install`, you can run tests by executing:

    php bin/phpunit

or executing:

    composer test

---
Todo:
- do more tests: figure out some more cases and edge cases and add them to the current test suite

Done:
- handled most common `gitignore`, including the ones:
    - including subfolders, eg.`foo/bar`
    - including `*`, eg.`foo/*/bar`
    - including `**`, eg.`foo/**/bar`
    - beginning with `!`
    - beginning with `#`
    - beginning with `\#`
---
