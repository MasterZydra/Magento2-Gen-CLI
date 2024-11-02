<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Dir;
use MasterZydra\GenCli\Helper\File;
use MasterZydra\GenCli\Helper\Question;
use MasterZydra\GenCli\Model\Module;
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
        private Module $module,
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

        $this->module->init($vendor, $module, $output);

        // Check if module already exists
        if ($this->module->exists()) {
            return 1;
        }

        $output->writeln('Generating module...');

        // Generate files
        if (!$this->module->copy()) {
            return 1;
        }

        $output->writeln('');
        $output->writeln("Module '$vendor/$module' was created.");
        return 0;
    }
}
