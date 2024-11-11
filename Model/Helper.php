<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation
// phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
// phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting.Missing

namespace MasterZydra\GenCli\Model;

use MasterZydra\GenCli\Helper\Dir;
use MasterZydra\GenCli\Helper\File;
use Symfony\Component\Console\Output\OutputInterface;

class Helper
{
    private ?Module $module;
    private string $name;
    private ?OutputInterface $output;

    private string $helperPath;

    /** @inheritdoc */
    public function __construct(
        private Dir $dir,
        private File $file,
    ) {
    }

    /** Init helper model */
    public function init(
        Module $module,
        string $name,
    ): void {
        $this->module = $module;
        $this->name = $name;
        $this->output = $module->output();

        $this->helperPath = $this->file->join($this->module->path(), 'Helper', $this->name . '.php');
    }

    /** Check if helper alread exists */
    public function exists(): bool
    {
        if ($this->file->exists($this->helperPath)) {
            $this->output->writeln('Helper \'' . $this->name . '\' already exists!');
            return true;
        }
        return false;
    }

    /** Copy templates */
    public function copy(): bool
    {
        if (!$this->file->copyTemplate(
            $this->file->join('Helper', 'Data.php.template'),
            $this->helperPath,
            [
                '{{ vendor }}' => $this->module->vendor(),
                '{{ module }}' => $this->module->module(),
                '{{ name }}' => $this->name,
            ],
        )) {
            $this->output->writeln('An error occured while creating \'' . $this->file->join('Helper', $this->name . '.php') . '\'');
            return false;
        }

        return true;
    }
}
