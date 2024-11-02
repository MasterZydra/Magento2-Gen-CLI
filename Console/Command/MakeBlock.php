<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Dir;
use MasterZydra\GenCli\Helper\File;
use MasterZydra\GenCli\Helper\Question;
use MasterZydra\GenCli\Model\Block;
use MasterZydra\GenCli\Model\Module;
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
        private Dir $dir,
        private File $file,
        private Question $question,
        private Module $module,
        private Block $block,
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

        $this->module->init($vendor, $module, $output);

        // Check if module exists
        if (!$this->module->exists(false)) {
            $output->writeln("Module '$vendor/$module' does not exist!");
            return 1;
        }

        // User inputs
        $section = $input->getArgument(self::SECTION);
        if (empty($section)) {
            $section = $this->question->ask($input, $output, 'Section name (e.g. \'Index\'): ', null);
            if (empty($section)) {
                $output->writeln('Section name is required!');
                return 1;
            }
        }

        $action = $input->getArgument(self::ACTION);
        if (empty($action)) {
            $action = $this->question->ask($input, $output, 'Action name (e.g. \'Index\'): ', null);
            if (empty($action)) {
                $output->writeln('Action name is required!');
                return 1;
            }
        }

        $output->writeln('');

        $this->block->init($this->module, $section, $action);

        // Check if block already exists
        if ($this->block->exists()) {
            return 1;
        }

        $output->writeln('Generating block...');

        // Generate files
        if (!$this->block->copy()) {
            return 1;
        }

        $output->writeln('');
        $output->writeln("Block '$section/$action' was created.");
        return 0;
    }
}
