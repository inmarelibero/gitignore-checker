<?php

declare(strict_types=1);

namespace Inmarelibero\GitIgnoreChecker\Tests\Model\GitIgnore;

use Inmarelibero\GitIgnoreChecker\Model\GitIgnore\File;
use Inmarelibero\GitIgnoreChecker\Model\RelativePath;
use Inmarelibero\GitIgnoreChecker\Tests\AbstractTestCase;

/**
 * Class GitIgnoreFileTest
 * @package Inmarelibero\GitIgnoreChecker\Tests\Model
 */
class FileTest extends AbstractTestCase
{
    /**
     * @covers \Inmarelibero\GitIgnoreChecker\Model\GitIgnore\File::buildFromRelativePathContainingGitIgnore()
     */
    public function testBuildFromRelativePathContainingGitIgnore()
    {
        $repository = $this->getTestRepository();

        $gitIgnoreFile = File::buildFromRelativePathContainingGitIgnore(new RelativePath($repository, '/'));
        $this->assertInstanceOf(File::class, $gitIgnoreFile);

        $this->assertCount(2, $gitIgnoreFile->getRules());
    }

    /**
     * @covers \Inmarelibero\GitIgnoreChecker\Model\GitIgnore\File::buildFromContent()
     */
    public function testBuildFromContent()
    {
        $repository = $this->getTestRepository();

        $gitIgnoreFile = File::buildFromContent(new RelativePath($repository, '/'), <<<EOF
foo
bar

baz
EOF
);
        $this->assertInstanceOf(File::class, $gitIgnoreFile);

        $this->assertCount(3, $gitIgnoreFile->getRules());
    }
    /**
     *
     */
    public function testIsPathIgnored()
    {
        // test "target/": folder (due to the trailing /) recursively
        $this->doTestSingleIsPathIgnored(
            <<<EOF
README/
!README/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!README/
README/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/
!foo/
EOF
            , '/foo', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/
foo/
EOF
            , '/foo', true);


        $this->doTestSingleIsPathIgnored(
            <<<EOF
bar_folder/
!bar_folder/
EOF
            , '/foo/bar_folder', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!bar_folder/
bar_folder/
EOF
            , '/foo/bar_folder', true);

        // test "target": file or folder named target recursively
        $this->doTestSingleIsPathIgnored(
            <<<EOF
README
!README
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!README
README
EOF
            , '/README', true);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo
!foo
EOF
            , '/foo', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo
foo
EOF
            , '/foo', true);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
bar_folder
!bar_folder
EOF
            , '/foo/bar_folder', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!bar_folder
bar_folder
EOF
            , '/foo/bar_folder', true);

        // test "/target": file or folder named target in the top-most directory (due to the leading /)
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/README
!/README
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/README
/README
EOF
            , '/README', true);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo
!/foo
EOF
            , '/foo', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo
/foo
EOF
            , '/foo', true);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo
!/foo
EOF
            , '/foo/bar_folder', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo
/foo
EOF
            , '/foo/bar_folder', true);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/bar_folder
/bar_folder
EOF
            , '/foo/bar_folder', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/bar_folder
!/bar_folder
EOF
            , '/foo/bar_folder', false);

        // test "/target/": folder named target in the top-most directory (leading and trailing /)
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/README/
/README/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/README/
!/README/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/
/foo/
EOF
, '/foo', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/
!/foo/
EOF
, '/foo', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/
/foo/
EOF
, '/foo/bar_folder', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/
!/foo/
EOF
, '/foo/bar_folder', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/bar_folder/
/bar_folder/
EOF
            , '/foo/bar_folder', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/bar_folder/
!/bar_folder/
EOF
            , '/foo/bar_folder', false);

        // test "*.class": every file or folder ending with .class recursively
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!*.md
*.md
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
*.md
!*.md
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!*.md
*.md
EOF
            , '/README.md', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
*.md
!*.md
EOF
            , '/README.md', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!*.md
*.md
EOF
            , '/foo/README.md', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
*.md
!*.md
EOF
            , '/foo/README.md', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/*.md
/*.md
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/*.md
!/*.md
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/*.md
/*.md
EOF
            , '/README.md', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/*.md
!/*.md
EOF
            , '/README.md', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/*.md
/*.md
EOF
            , '/foo/README.md', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/*.md
!/*.md
EOF
            , '/foo/README.md', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/*.md
/*.md
EOF
            , '/.README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/*.md
!/*.md
EOF
            , '/.README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/*.md
/*.md
EOF
            , '/.README.md', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/*.md
!/*.md
EOF
            , '/.README.md', false);
        $this->doTestSingleIsPathIgnored('/*.md', '/foo/.README.md', false);

        // test "#comment": nothing, this is a comment (first character is a #)
        // @todo restore: throws exception on __construct
//        $this->doTestSingleIsPathIgnored(false, '/README', '# comment');
//        $this->doTestSingleIsPathIgnored(false, '/foo', '# comment');
//        $this->doTestSingleIsPathIgnored(false, '/foo/bar_folder', '# comment');

        // test "\#comment": every file or folder with name #comment (\ for escaping)
//        $this->doTestSingleIsPathIgnored(true, '/#README', '\#README');
//        $this->doTestSingleIsPathIgnored(false, '/foo', '\# comment');
//        $this->doTestSingleIsPathIgnored(false, '/foo/bar_folder', '\# comment');

        // test "target/logs/": every folder named logs which is a subdirectory of a folder named target
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/bar_folder/
foo/bar_folder/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/bar_folder/
!foo/bar_folder/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/bar_folder/
foo/bar_folder/
EOF
            , '/foo', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/bar_folder/
!foo/bar_folder/
EOF
            , '/foo', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/bar_folder/
foo/bar_folder/
EOF
            , '/foo/bar_folder', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/bar_folder/
!foo/bar_folder/
EOF
            , '/foo/bar_folder', false);

        // test "target/*/logs/": every folder named logs two levels under a folder named target (* doesn’t include /)
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/*/bar_subfolder/
foo/*/bar_subfolder/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/*/bar_subfolder/
!foo/*/bar_subfolder/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/*/bar_subfolder/
/foo/*/bar_subfolder/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/*/bar_subfolder/
!/foo/*/bar_subfolder/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/*/bar_subfolder/
foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/*/bar_subfolder/
!foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/*/bar_subfolder/
/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/*/bar_subfolder/
!/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/*/bar_subfolder/
foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/*/bar_subfolder/
!foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/*/bar_subfolder/
/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/*/bar_subfolder/
!/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);

        // test "target/**/logs/": every folder named logs somewhere under a folder named target (** includes /)
        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/*/bar_subfolder/
foo/*/bar_subfolder/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/*/bar_subfolder/
!foo/*/bar_subfolder/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/*/bar_subfolder/
/foo/*/bar_subfolder/
EOF
            , '/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/*/bar_subfolder/
!/foo/*/bar_subfolder/
EOF
            , '/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/*/bar_subfolder/
foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/*/bar_subfolder/
!foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/*/bar_subfolder/
/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', true);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/*/bar_subfolder/
!/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/bar_subfolder/', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!foo/*/bar_subfolder/
foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
foo/*/bar_subfolder/
!foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);

        $this->doTestSingleIsPathIgnored(
            <<<EOF
!/foo/*/bar_subfolder/
/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);
        $this->doTestSingleIsPathIgnored(
            <<<EOF
/foo/*/bar_subfolder/
!/foo/*/bar_subfolder/
EOF
            , '/foo/bar_folder/README', false);
    }

    /**
     * @param string $content
     * @param string $relativePath
     * @param bool$expectedMatch
     */
    private function doTestSingleIsPathIgnored(string $content, string $relativePath, bool $expectedMatch) : void
    {
        if (!is_bool($expectedMatch)) {
            throw new \InvalidArgumentException("ExpectedMatch must be a boolean.");
        }

        $gitIgnoreFile = File::buildFromContent(new RelativePath($this->getTestRepository(), '/'), $content);

        $relativePath = new RelativePath($this->getTestRepository(), $relativePath);

        $this->assertEquals(
            $expectedMatch,
            $gitIgnoreFile->isPathIgnored($relativePath),
            $this->getErrorMessageForIsPathIgnored($expectedMatch, $gitIgnoreFile, $relativePath)
        );
    }

    /**
     * @param $expectedMatch
     * @param File $gitIgnoreFile
     * @param $path
     * @return string
     */
    private function getErrorMessageForIsPathIgnored(bool $expectedMatch, File $gitIgnoreFile, RelativePath $relativePath)
    {
        return sprintf(<<<EOF
Path "%s" %s have been matched against .gitignore file with content:
%s
EOF

            ,
            $relativePath->getPath(),
            ($expectedMatch === true) ? 'should' : 'shouldn\'t',
            $gitIgnoreFile->getContent()
        );
    }
}
