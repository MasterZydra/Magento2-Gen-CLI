<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Dir;
use MasterZydra\GenCli\Helper\File;
use MasterZydra\GenCli\Helper\Question;
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
        private Dir $dir,
        private File $file,
        private Question $question,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /** @inheritdoc */
    protected function configure(): void
    {
        $this->setName('make:module');
        $this->setDescription('Create a new module');
        $this->addArgument(self::VENDOR, InputArgument::OPTIONAL, 'Vendor name (e.g. \'Magento\')');
        $this->addArgument(self::MODULE, InputArgument::OPTIONAL, 'Module name (e.g. \'Sales\')');
        parent::configure();
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // User inputs
        $vendor = $input->getArgument(self::VENDOR);
        if (empty($vendor)) {
            $vendor = $this->question->ask($input, $output, 'Vendor name (e.g. \'Magento\'): ', false);
            if (empty($vendor)) {
                $output->writeln('Vendor name is required!');
                return 1;
            }
        }

        $module = $input->getArgument(self::MODULE);
        if (empty($module)) {
            $module = $this->question->ask($input, $output, 'Module name (e.g. \'Sales\'): ', null);
            if (empty($module)) {
                $output->writeln('Module name is required!');
                return 1;
            }
        }

        $output->writeln('');

        $modulePath = $this->file->join($this->dir->appCode(), $vendor, $module);
        $moduleName = $vendor. '_' . $module;

        // Create module folder
        if ($this->file->exists($modulePath)) {
            $output->writeln("Module '$vendor/$module' already exists!");
            return 1;
        }

        if (!$this->file->mkDir($modulePath)) {
            $output->writeln('An error occured while creating the module directory!');
            return 1;
        }

        $output->writeln('Generating module...');

        // Generate files
        // Source: https://experienceleague.adobe.com/en/docs/commerce-learn/tutorials/backend-development/create-module

        if (!$this->file->copyTemplate(
            $this->file->join($this->dir->template(), 'registration.php.txt'),
            $this->file->join($modulePath, 'registration.php'),
            ['{{ module_name }}' => $moduleName],
        )) {
            $output->writeln('An error occured while creating \'registration.php\'');
            return 1;
        }

        if (!$this->file->copyTemplate(
            $this->file->join($this->dir->template(), 'etc', 'module.xml'),
            $this->file->join($modulePath, 'etc', 'module.xml'),
            ['{{ module_name }}' => $moduleName],
        )) {
            $output->writeln('An error occured while creating \'ect/module.xml\'');
            return 1;
        }

        $output->writeln('');
        $output->writeln("Module '$vendor/$module' was created.");
        return 0;
    }
}
