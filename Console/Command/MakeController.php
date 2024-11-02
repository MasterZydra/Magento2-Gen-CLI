<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Console\Command;

use MasterZydra\GenCli\Helper\Dir;
use MasterZydra\GenCli\Helper\File;
use MasterZydra\GenCli\Helper\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeController extends Command
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
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /** @inheritdoc */
    protected function configure(): void
    {
        $this->setName('make:controller');
        $this->setDescription('Create a new controller');
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

        $modulePath = $this->file->join($this->dir->appCode(), $vendor, $module);

        // Check if module exists
        if (!$this->file->exists($modulePath)) {
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

        $controllerPath = $this->file->join($modulePath, 'Controller', $section, $action . '.php');

        // Check if controller already exists
        if ($this->file->exists($controllerPath)) {
            $output->writeln("Controller '$section/$action' already exists!");
            return 1;
        }

        $output->writeln('Generating controller...');

        // Generate file
        if (!$this->file->copyTemplate(
            $this->file->join($this->dir->template(), 'Controller', 'Section', 'Action.php.txt'),
            $controllerPath,
            [
                '{{ vendor }}' => $vendor,
                '{{ module }}' => $module,
                '{{ section }}' => $section,
                '{{ action }}' => $action,
            ],
        )) {
            $output->writeln('An error occured while creating \'' . $this->file->join('Controller', $section, $action . '.php') . '\'');
            return 1;
        }

        $output->writeln('');
        $output->writeln("Controller '$section/$action' was created.");
        return 0;
    }
}
