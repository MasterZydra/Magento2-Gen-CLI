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

class Controller
{
    private ?Module $module;
    private string $section;
    private string $action;
    private ?OutputInterface $output;

    private string $controllerPath;
    private string $frontendXmlPath;

    /** @inheritdoc */
    public function __construct(
        private Dir $dir,
        private File $file,
    ) {
    }

    /** Init controller model */
    public function init(
        Module $module,
        string $section,
        string $action,
    ): void {
        $this->module = $module;
        $this->section = $section;
        $this->action = $action;
        $this->output = $module->output();

        $this->controllerPath = $this->file->join($this->module->path(), 'Controller', $this->section, $this->action . '.php');
        $this->frontendXmlPath = $this->file->join($this->module->path(), 'etc', 'frontend', 'routes.xml');
    }

    /** Check if controller alread exists */
    public function exists(): bool
    {
        if ($this->file->exists($this->controllerPath)) {
            $this->output->writeln('Controller \'' . $this->section . '/' . $this->action . '\' already exists!');
            return true;
        }
        return false;
    }

    /** Copy templates */
    public function copy(): bool
    {
        if (!$this->file->copyTemplate(
            $this->file->join('Controller', 'Section', 'Action.php.template'),
            $this->controllerPath,
            [
                '{{ vendor }}' => $this->module->vendor(),
                '{{ module }}' => $this->module->module(),
                '{{ section }}' => $this->section,
                '{{ action }}' => $this->action,
            ],
        )) {
            $this->output->writeln('An error occured while creating \'' . $this->file->join('Controller', $this->section, $this->action . '.php') . '\'');
            return false;
        }

        if (!$this->file->exists($this->frontendXmlPath)) {
            if (!$this->file->copyTemplate(
                $this->file->join('etc', 'frontend', 'routes.xml.template'),
                $this->frontendXmlPath,
                [
                    '{{ module_name }}' => $this->module->moduleName(),
                    '{{ module_name_lower }}' => strtolower($this->module->moduleName()),
                ],
            )) {
                $this->output->writeln('An error occured while creating \'' . $this->file->join('etc', 'frontend', 'routes.xml') . '\'');
                return false;
            }
        }

        return true;
    }
}
