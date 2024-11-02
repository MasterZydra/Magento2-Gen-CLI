<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

class File extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @inheritdoc */
    public function __construct(
        Context $context,
        private FileDriver $fileDriver,
    ) {
        parent::__construct($context);
    }

    /** Copy source into destination */
    public function copy(string $source, string $destination): bool
    {
        return $this->fileDriver->copy($source, $destination);
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

    /** Does file or directory exist in file system */
    public function exists(string $path): bool
    {
        return $this->fileDriver->isExists($path);
    }

    /** Tells whether the filename is a regular directory */
    public function isDir(string $path): bool
    {
        return $this->fileDriver->isDirectory($path);
    }

    /** Join the given paths with the directory separator */
    public function join(string ...$paths): string
    {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    /** Create directory */
    public function mkDir(string $path): bool
    {
        return $this->fileDriver->createDirectory($path);
    }

    /** Returns parent directory's path */
    public function parentDir(string $path): string
    {
        return $this->fileDriver->getParentDirectory($path);
    }

    /** Retrieve file contents from given path */
    public function read(string $path): string
    {
        return $this->fileDriver->fileGetContents($path);
    }

    /** Write contents to file in given path */
    public function write(string $path, string $content, $mode = null): int
    {
        return $this->fileDriver->filePutContents($path, $content, $mode);
    }
}
