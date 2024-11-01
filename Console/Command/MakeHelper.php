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

class MakeHelper extends Command
{
    private const VENDOR = 'vendor';
    private const MODULE = 'module';
    private const NAME = 'name';

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

        // Check if module exists
        if (!$this->helper->exists($modulePath)) {
            $output->writeln("Module '$vendor/$module' does not exist!");
            return 1;
        }

        // User inputs
        $name = $input->getArgument(self::NAME);
        if (empty($name)) {
            $name = $this->helper->askQuestion($input, $output, 'Helper name (e.g. \'Sync\'): ', null);
            if (empty($name)) {
                $output->writeln('Helper name is required!');
                return 1;
            }
        }

        $output->writeln('');

        $helperPath = $this->helper->joinPaths($modulePath, 'Helper', $name . '.php');

        // Check if helper already exists
        if ($this->helper->exists($helperPath)) {
            $output->writeln("Helper '$name' already exists!");
            return 1;
        }

        $output->writeln('Generating helper...');

        // Generate file
        if (!$this->helper->copyTemplate(
            $this->helper->joinPaths($this->helper->templatePath(), 'Helper', 'Data.php.txt'),
            $helperPath,
            [
                '{{ vendor }}' => $vendor,
                '{{ module }}' => $module,
                '{{ name }}' => $name,
            ],
        )) {
            $output->writeln('An error occured while creating \''. $this->helper->joinPaths('Helper', $name . '.php') . '\'');
            return 1;
        }

        $output->writeln('');
        $output->writeln("Helper '$name' was created.");
        return 0;
    }
}
