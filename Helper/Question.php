<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Helper;

use Magento\Framework\App\Helper\Context;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as SymfonyQuestion;

class Question extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @inheritdoc */
    public function __construct(
        Context $context,
        private QuestionHelper $questionHelper,
    ) {
        parent::__construct($context);
    }

    /** Asks a question to the user */
    public function ask(
        InputInterface $input,
        OutputInterface $output,
        string $question,
        string|bool|int|float|null $default = null
    ): mixed {
        return $this->questionHelper->ask($input, $output, new SymfonyQuestion($question, $default));
    }
}
