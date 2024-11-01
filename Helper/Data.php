<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Data extends AbstractHelper
{
    /** @inheritdoc */
    public function __construct(
        Context $context,
        private QuestionHelper $questionHelper,
        private DirectoryList $dirList,
        private File $fileHelper,
    ) {
        parent::__construct($context);
    }

    // MARK: Question helper

    /** Asks a question to the user. */
    public function askQuestion(
        InputInterface $input,
        OutputInterface $output,
        string $question,
        string|bool|int|float|null $default = null
    ): mixed {
        return $this->questionHelper->ask($input, $output, new Question($question, $default));
    }

    // MARK: Path helper

    /** Gets a filesystem path of the template directory */
    public function templatePath(): string
    {
        return $this->joinPaths(__DIR__, '..', 'Template');
    }

    /** Gets a filesystem path of the root directory */
    public function rootPath(): string
    {
        return $this->dirList->getRoot();
    }

    /** Gets a filesystem path of the app directory */
    public function appPath(): string
    {
        return $this->dirList->getPath('app');
    }

    /** Gets a filesystem path of the app/code directory */
    public function appCodePath(): string
    {
        return $this->dirList->getPath('app') . DIRECTORY_SEPARATOR . 'code';
    }

    /** Join the given paths with the directory separator */
    public function joinPaths(string ...$paths): string
    {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    // MARK: File helper

    /** Returns parent directory's path */
    public function parentDir(string $path): string
    {
        return $this->fileHelper->getParentDirectory($path);
    }

    /** Copy source into destination */
    public function copy(string $source, string $destination): bool
    {
        return $this->fileHelper->copy($source, $destination);
    }

    /** Copy template from source to destination and replace the placeholders */
    public function copyTemplate(string $source, string $destination, array $placeholders = []): bool
    {
        // Read file content
        $content = $this->read($source);
        // Replace placeholders
        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        $path = $this->parentDir($destination);
        if (!$this->exists($path)) {
            if (!$this->mkdir($path)) {
                return false;
            }
        }
        // Write file content
        return $this->write($destination, $content) > 0;
    }

    /** Retrieve file contents from given path */
    public function read(string $path): string
    {
        return $this->fileHelper->fileGetContents($path);
    }

    /** Write contents to file in given path */
    public function write(string $path, string $content, $mode = null): int
    {
        return $this->fileHelper->filePutContents($path, $content, $mode);
    }

    /** Does file or directory exist in file system */
    public function exists(string $path): bool
    {
        return $this->fileHelper->isExists($path);
    }

    /** Tells whether the filename is a regular directory */
    public function isDir(string $path): bool
    {
        return $this->fileHelper->isDirectory($path);
    }

    /** Create directory */
    public function mkDir(string $path): bool
    {
        return $this->fileHelper->createDirectory($path);
    }
}
