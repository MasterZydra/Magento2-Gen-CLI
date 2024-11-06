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

class Module
{
    private string $vendor;
    private string $module;
    private ?OutputInterface $output;

    private string $moduleName;
    private string $modulePath;
    private string $registrationPath;
    private string $moduleXmlPath;

    /** @inheritdoc */
    public function __construct(
        private Dir $dir,
        private File $file,
    ) {
    }

    /** Init module model */
    public function init(
        string $vendor,
        string $module,
        OutputInterface $output,
    ): void {
        $this->vendor = $vendor;
        $this->module = $module;
        $this->output = $output;

        $this->moduleName = $this->vendor. '_' . $this->module;
        $this->modulePath = $this->file->join($this->dir->appCode(), $this->vendor, $this->module);
        $this->registrationPath = $this->file->join($this->modulePath, 'registration.php');
        $this->moduleXmlPath = $this->file->join($this->modulePath, 'etc', 'module.xml');
    }

    /** Get vendor name */
    public function vendor(): string
    {
        return $this->vendor;
    }

    /** Get module name */
    public function module(): string
    {
        return $this->module;
    }

    /** Get module name ("vendor_module") */
    public function moduleName(): string
    {
        return $this->moduleName;
    }

    /** Get module path */
    public function path(): string
    {
        return $this->modulePath;
    }

    /** Get output interface */
    public function output(): OutputInterface
    {
        return $this->output;
    }

    /** Check if module alread exists */
    public function exists(bool $writeOutput = true): bool
    {
        if ($this->file->exists($this->registrationPath)) {
            if ($writeOutput) {
                $this->output->writeln('\'registration.php\' already exists!');
            }
            return true;
        }
        if ($this->file->exists($this->moduleXmlPath)) {
            if ($writeOutput) {
                $this->output->writeln('\'ect/module.xml\' already exists!');
            }
            return true;
        }
        return false;
    }

    /** Copy templates */
    public function copy(): bool
    {
        // Source: https://experienceleague.adobe.com/en/docs/commerce-learn/tutorials/backend-development/create-module

        if (!$this->file->copyTemplate(
            $this->file->join($this->dir->template(), 'composer.json.template'),
            $this->file->join($this->modulePath, 'composer.json'),
            [
                '{{ vendor }}' => $this->vendor(),
                '{{ vendor_lower }}' => strtolower($this->vendor()),
                '{{ module }}' => $this->module(),
                '{{ module_lower }}' => strtolower($this->module()),
            ],
        )) {
            $this->output->writeln('An error occured while creating \'composer.json\'');
            return false;
        }

        if (!$this->file->copyTemplate(
            $this->file->join($this->dir->template(), 'registration.php.template'),
            $this->file->join($this->modulePath, 'registration.php'),
            ['{{ module_name }}' => $this->moduleName],
        )) {
            $this->output->writeln('An error occured while creating \'registration.php\'');
            return false;
        }

        if (!$this->file->copyTemplate(
            $this->file->join($this->dir->template(), 'etc', 'module.xml.template'),
            $this->file->join($this->modulePath, 'etc', 'module.xml'),
            ['{{ module_name }}' => $this->moduleName],
        )) {
            $this->output->writeln('An error occured while creating \'ect/module.xml\'');
            return false;
        }

        return true;
    }
}
