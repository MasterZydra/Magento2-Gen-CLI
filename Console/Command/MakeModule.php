<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Data;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModule extends Command
{
    private const VENDOR = 'vendor';
    private const MODULE = 'module';

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
        $this->setName('make:module');
        $this->setDescription('Create a new Magento2 module');
        $this->addArgument(self::VENDOR, InputArgument::OPTIONAL, 'Vendor name');
        $this->addArgument(self::MODULE, InputArgument::OPTIONAL, 'Module name');
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

        // Create module folder
        if ($this->helper->exists($modulePath)) {
            $output->writeln("The module '$vendor/$module' already exists!");
            return 1;
        }

        if (!$this->helper->mkDir($modulePath)) {
            $output->writeln('An error occured while creating the module directory!');
            return 1;
        }

        $output->writeln('Generating files...');

        // Generate files
        // Source: https://experienceleague.adobe.com/en/docs/commerce-learn/tutorials/backend-development/create-module

        if (!$this->helper->copyTemplate(
            $this->helper->joinPaths($this->helper->templatePath(), 'registration.php'),
            $this->helper->joinPaths($modulePath, 'registration.php'),
            ['{{ modulename }}' => $moduleName],
        )) {
            $output->writeln('An error occured while creating \'registration.php\'');
            return 1;
        }

        if (!$this->helper->copyTemplate(
            $this->helper->joinPaths($this->helper->templatePath(), 'etc', 'module.xml'),
            $this->helper->joinPaths($modulePath, 'etc', 'module.xml'),
            ['{{ modulename }}' => $moduleName],
        )) {
            $output->writeln('An error occured while creating \'ect/module.xml\'');
            return 1;
        }

        $output->writeln("Module '$vendor/$module' was created.");
        return 0;
    }
}
