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

class Block
{
    private ?Module $module;
    private string $section;
    private string $action;
    private ?OutputInterface $output;

    private string $blockPath;
    private string $layoutName;
    private string $layoutPath;
    private string $templatePath;

    /** @inheritdoc */
    public function __construct(
        private Dir $dir,
        private File $file,
    ) {
    }

    /** Init block model */
    public function init(
        Module $module,
        string $section,
        string $action,
    ): void {
        $this->module = $module;
        $this->section = $section;
        $this->action = $action;
        $this->output = $module->output();

        $this->blockPath = $this->file->join($this->module->path(), 'Block', $this->section, $this->action . '.php');
        $this->layoutName = strtolower($this->module->vendor()) . '_' . strtolower($this->module->module()) . '_' . strtolower($this->section) . '_' . strtolower($this->action) . '.xml';
        $this->layoutPath = $this->file->join($this->module->path(), 'view', 'frontend', 'layout', $this->layoutName);
        $this->templatePath = $this->file->join($this->module->path(), 'view', 'frontend', 'templates', strtolower($this->section), strtolower($this->action) . '.phtml');
    }

    /** Check if block alread exists */
    public function exists(): bool
    {
        if ($this->file->exists($this->blockPath)) {
            $this->output->writeln('Block \'' . $this->section . '/' . $this->action . '\' already exists!');
            return true;
        }
        if ($this->file->exists($this->layoutPath)) {
            $this->output->writeln('Layout \'' . $this->layoutName . '\' already exists!');
            return true;
        }
        if ($this->file->exists($this->templatePath)) {
            $this->output->writeln('Template \'' . $this->section . '/' . $this->action .'.phtml\' already exists!');
            return true;
        }
        return false;
    }

    /** Copy templates */
    public function copy(): bool
    {
        if (!$this->file->copyTemplate(
            $this->file->join('Block', 'Section', 'Action.php.template'),
            $this->blockPath,
            [
                '{{ vendor }}' => $this->module->vendor(),
                '{{ module }}' => $this->module->module(),
                '{{ section }}' => $this->section,
                '{{ action }}' => $this->action,
            ],
        )) {
            $this->output->writeln('An error occured while creating \'' . $this->file->join('Block', $this->section, $this->action . '.php') . '\'');
            return false;
        }

        if (!$this->file->copyTemplate(
            $this->file->join('view', 'frontend', 'layout', 'vendor_module_section_action.xml.template'),
            $this->layoutPath,
            [
                '{{ module_name }}' => $this->module->moduleName(),
                '{{ vendor }}' => $this->module->vendor(),
                '{{ module }}' => $this->module->module(),
                '{{ section }}' => $this->section,
                '{{ section_lower }}' => strtolower($this->section),
                '{{ action }}' => $this->action,
                '{{ action_lower }}' => strtolower($this->action),
            ],
        )) {
            $this->output->writeln('An error occured while creating \'' . $this->file->join('view', 'frontend', 'layout', $this->layoutName) . '\'');
            return false;
        }

        if (!$this->file->copyTemplate(
            $this->file->join('view', 'frontend', 'templates', 'section', 'action.phtml.template'),
            $this->templatePath,
            [
                '{{ vendor }}' => $this->module->vendor(),
                '{{ module }}' => $this->module->module(),
                '{{ section }}' => $this->section,
                '{{ section_lower }}' => strtolower($this->section),
                '{{ action }}' => $this->action,
                '{{ action_lower }}' => strtolower($this->action),
            ],
        )) {
            $this->output->writeln('An error occured while creating \'' . $this->file->join('view', 'frontend', 'templates', strtolower($this->section), strtolower($this->action) . '.phtml') . '\'');
            return false;
        }

        return true;
    }
}
