<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCommand extends \Symfony\Component\Console\Command\Command
{
    private const VENDOR = 'vendor';
    private const MODULE = 'module';
    private const NAME = 'name';
    private const COMMAND = 'cmd';
    private const DESCRIPTION = 'description';

    /** @inheritdoc */
    public function __construct(
        private \MasterZydra\GenCli\Helper\Dir $dir,
        private \MasterZydra\GenCli\Helper\File $file,
        private \MasterZydra\GenCli\Helper\Question $question,
        private \MasterZydra\GenCli\Model\Module $module,
        private \MasterZydra\GenCli\Model\Command $command,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /** @inheritdoc */
    protected function configure(): void
    {
        $this->setName('make:command');
        $this->setDescription('Create a new command');
        $this->addArgument(self::VENDOR, InputArgument::OPTIONAL, 'Vendor name (e.g. \'Magento\')');
        $this->addArgument(self::MODULE, InputArgument::OPTIONAL, 'Module name (e.g. \'Sales\')');
        $this->addArgument(self::NAME, InputArgument::OPTIONAL, 'Command name (e.g. \'SyncSales\')');
        $this->addArgument(self::COMMAND, InputArgument::OPTIONAL, 'Command (e.g. \'sales:sync\')');
        $this->addArgument(self::DESCRIPTION, InputArgument::OPTIONAL, 'Command description (e.g. \'Sync sales with Cloud\')');
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
            $createModule = $this->question->ask($input, $output, 'Do you want to create it? (y/N) ', null);
            if (strtolower($createModule ?? '') !== 'y' || !$this->module->copy()) {
                return 1;
            }
        }

        // User inputs
        $name = $input->getArgument(self::NAME);
        if (empty($name)) {
            $name = $this->question->ask($input, $output, 'Command name (e.g. \'SyncSales\'): ', null);
            if (empty($name)) {
                $output->writeln('Command name is required!');
                return 1;
            }
        }

        $command = $input->getArgument(self::COMMAND);
        if (empty($command)) {
            $command = $this->question->ask($input, $output, 'Command (e.g. \'sales:sync\'): ', null);
            if (empty($command)) {
                $output->writeln('Command is required!');
                return 1;
            }
        }

        $description = $input->getArgument(self::DESCRIPTION);
        if (empty($description)) {
            $description = $this->question->ask($input, $output, 'Command description (e.g. \'Sync sales with Cloud\'): ', null);
            if (empty($description)) {
                $output->writeln('Command description is required!');
                return 1;
            }
        }

        $output->writeln('');

        $this->command->init($this->module, $name, $command, $description);

        // Check if command already exists
        if ($this->command->exists()) {
            return 1;
        }

        $output->writeln('Generating command...');

        // Generate file
        if (!$this->command->copy()) {
            return 1;
        }

        $output->writeln('');
        $output->writeln("Command '$name' was created.");
        return 0;
    }
}
