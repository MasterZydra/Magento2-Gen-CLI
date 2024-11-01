<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Data;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeBlock extends Command
{
    private const VENDOR = 'vendor';
    private const MODULE = 'module';
    private const SECTION = 'section';
    private const ACTION = 'action';

    /** @inheritdoc */
    public function __construct(
        private Data $helper,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /** @inheritdoc */
    protected function configure(): void
    {
        $this->setName('make:block');
        $this->setDescription('Create a new block');
        $this->addArgument(self::VENDOR, InputArgument::OPTIONAL, 'Vendor name (e.g. \'Magento\')');
        $this->addArgument(self::MODULE, InputArgument::OPTIONAL, 'Module name (e.g. \'Sales\')');
        $this->addArgument(self::SECTION, InputArgument::OPTIONAL, 'Section name (e.g. \'Index\')');
        $this->addArgument(self::ACTION, InputArgument::OPTIONAL, 'Action name (e.g. \'Index\')');
        parent::configure();
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // User inputs
        $vendor = $input->getArgument(self::VENDOR);
        if (empty($vendor)) {
            $vendor = $this->helper->askQuestion($input, $output, 'Vendor name (e.g. \'Magento\'): ', false);
            if (empty($vendor)) {
                $output->writeln('Vendor name is required!');
                return 1;
            }
        }

        $module = $input->getArgument(self::MODULE);
        if (empty($module)) {
            $module = $this->helper->askQuestion($input, $output, 'Module name (e.g. \'Sales\'): ', null);
            if (empty($module)) {
                $output->writeln('Module name is required!');
                return 1;
            }
        }

        $modulePath = $this->helper->joinPaths($this->helper->appCodePath(), $vendor, $module);
        $moduleName = $vendor. '_' . $module;

        // Check if module exists
        if (!$this->helper->exists($modulePath)) {
            $output->writeln("Module '$vendor/$module' does not exist!");
            return 1;
        }

        // User inputs
        $section = $input->getArgument(self::SECTION);
        if (empty($section)) {
            $section = $this->helper->askQuestion($input, $output, 'Section name (e.g. \'Index\'): ', null);
            if (empty($section)) {
                $output->writeln('Section name is required!');
                return 1;
            }
        }

        $action = $input->getArgument(self::ACTION);
        if (empty($action)) {
            $action = $this->helper->askQuestion($input, $output, 'Action name (e.g. \'Index\'): ', null);
            if (empty($action)) {
                $output->writeln('Action name is required!');
                return 1;
            }
        }

        $output->writeln('');

        $blockPath = $this->helper->joinPaths($modulePath, 'Block', $section, $action . '.php');
        $layoutName = strtolower($vendor) . '_' . strtolower($module) . '_' . strtolower($section) . '_' . strtolower($action) . '.xml';
        $layoutPath = $this->helper->joinPaths($modulePath, 'view', 'frontend', 'templates', 'layout', $layoutName);
        $templatePath = $this->helper->joinPaths($modulePath, 'view', 'frontend', 'templates', $section, $action . '.phtml');

        // Check if block already exists
        if ($this->helper->exists($blockPath)) {
            $output->writeln("Block '$section/$action' already exists!");
            return 1;
        }
        if ($this->helper->exists($layoutPath)) {
            $output->writeln("Layout '$layoutName' already exists!");
            return 1;
        }
        if ($this->helper->exists($templatePath)) {
            $output->writeln("Template '$section/$action.phtml' already exists!");
            return 1;
        }

        $output->writeln('Generating block...');

        // Generate files
        if (!$this->helper->copyTemplate(
            $this->helper->joinPaths($this->helper->templatePath(), 'Block', 'Section', 'Action.php.txt'),
            $blockPath,
            [
                '{{ vendor }}' => $vendor,
                '{{ module }}' => $module,
                '{{ section }}' => $section,
                '{{ action }}' => $action,
            ],
        )) {
            $output->writeln('An error occured while creating \'' . $this->helper->joinPaths('Block', $section, $action . '.php') . '\'');
            return 1;
        }

        if (!$this->helper->copyTemplate(
            $this->helper->joinPaths($this->helper->templatePath(), 'view', 'frontend', 'templates', 'layout', 'vendor_module_section_action.xml'),
            $layoutPath,
            [
                '{{ module_name }}' => $moduleName,
                '{{ vendor }}' => $vendor,
                '{{ module }}' => $module,
                '{{ section }}' => $section,
                '{{ section_lower }}' => strtolower($section),
                '{{ action }}' => $action,
                '{{ action_lower }}' => strtolower($action),
            ],
        )) {
            $output->writeln('An error occured while creating \'' . $this->helper->joinPaths('view', 'frontend', 'templates', 'layout', $layoutName) . '\'');
            return 1;
        }

        if (!$this->helper->copyTemplate(
            $this->helper->joinPaths($this->helper->templatePath(), 'view', 'frontend', 'templates', 'section', 'action.phtml.txt'),
            $templatePath,
            [
                '{{ vendor }}' => $vendor,
                '{{ module }}' => $module,
                '{{ section }}' => $section,
                '{{ section_lower }}' => strtolower($section),
                '{{ action }}' => $action,
                '{{ action_lower }}' => strtolower($action),
            ],
        )) {
            $output->writeln('An error occured while creating \'' . $this->helper->joinPaths('view', 'frontend', 'templates', $section, $action . '.phtml') . '\'');
            return 1;
        }

        $output->writeln('');
        $output->writeln("Block '$section/$action' was created.");
        return 0;
    }
}