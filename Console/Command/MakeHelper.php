<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Dir;
use MasterZydra\GenCli\Helper\File;
use MasterZydra\GenCli\Helper\Question;
use MasterZydra\GenCli\Model\Helper;
use MasterZydra\GenCli\Model\Module;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeHelper extends Command
{
    private const VENDOR = 'vendor';
    private const MODULE = 'module';
    private const NAME = 'name';

    /** @inheritdoc */
    public function __construct(
        private Dir $dir,
        private File $file,
        private Question $question,
        private Module $module,
        private Helper $helper,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /** @inheritdoc */
    protected function configure(): void
    {
        $this->setName('make:helper');
        $this->setDescription('Create a new helper');
        $this->addArgument(self::VENDOR, InputArgument::OPTIONAL, 'Vendor name (e.g. \'Magento\')');
        $this->addArgument(self::MODULE, InputArgument::OPTIONAL, 'Module name (e.g. \'Sales\')');
        $this->addArgument(self::NAME, InputArgument::OPTIONAL, 'Helper name (e.g. \'Sync\')');
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
        $name = $input->getArgument(self::NAME);
        if (empty($name)) {
            $name = $this->question->ask($input, $output, 'Helper name (e.g. \'Sync\'): ', null);
            if (empty($name)) {
                $output->writeln('Helper name is required!');
                return 1;
            }
        }

        $output->writeln('');

        $this->helper->init($this->module, $name);

        // Check if helper already exists
        if ($this->helper->exists()) {
            return 1;
        }

        $output->writeln('Generating helper...');

        // Generate file
        if (!$this->helper->copy()) {
            return 1;
        }

        $output->writeln('');
        $output->writeln("Helper '$name' was created.");
        return 0;
    }
}
