<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation
// phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
// phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting.Missing

namespace MasterZydra\GenCli\Model;

class Command
{
    private ?Module $module;
    private string $name;
    private string $command;
    private string $description;
    private ?\Symfony\Component\Console\Output\OutputInterface $output;

    private string $commandPath;
    private string $diXmlPath;

    /** @inheritdoc */
    public function __construct(
        private \MasterZydra\GenCli\Helper\Dir $dir,
        private \MasterZydra\GenCli\Helper\File $file,
        private \MasterZydra\GenCli\Helper\Xml $xml,
    ) {
    }

    /** Init controller model */
    public function init(
        Module $module,
        string $name,
        string $command,
        string $description
    ): void {
        $this->module = $module;
        $this->name = $name;
        $this->command = $command;
        $this->description = $description;
        $this->output = $module->output();

        $this->commandPath = $this->file->join($this->module->path(), 'Console', 'Command', $this->name . '.php');
        $this->diXmlPath = $this->file->join($this->module->path(), 'etc', 'di.xml');
    }

    /** Check if controller alread exists */
    public function exists(): bool
    {
        if ($this->file->exists($this->commandPath)) {
            $this->output->writeln('Command \'' . $this->name . '.php\' already exists!');
            return true;
        }
        return false;
    }

    /** Copy templates */
    public function copy(): bool
    {
        if (!$this->file->copyTemplate(
            $this->file->join('Console', 'Command', 'Command.php.template'),
            $this->commandPath,
            [
                '{{ vendor }}' => $this->module->vendor(),
                '{{ module }}' => $this->module->module(),
                '{{ name }}' => $this->name,
                '{{ command }}' => $this->command,
                '{{ description }}' => $this->description,
            ],
        )) {
            $this->output->writeln('An error occured while creating \'' . $this->file->join('Console', 'Command', $this->name . '.php') . '\'');
            return false;
        }

        if (!$this->file->exists($this->diXmlPath)) {
            if (!$this->file->copyTemplate(
                $this->file->join('etc', 'di.xml.template'),
                $this->diXmlPath,
                [],
            )) {
                $this->output->writeln('An error occured while creating \'' . $this->file->join('etc', 'di.xml') . '\'');
                return false;
            }
        }

        return $this->addItemToDiXml();
    }

    private function addItemToDiXml(): bool
    {
        $dom = $this->xml->read($this->diXmlPath);
        if ($dom === null) {
            $this->output->writeln('An error occured while reading \'' . $this->file->join('etc', 'di.xml') . '\'');
            return false;
        }

        // <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">

        /** @var \DOMElement $configElement */
        $configElement = $dom->getElementsByTagName('config')->item(0);
        if (!$configElement) {
            $configElement = $this->xml->createElement($dom, 'config', attributes: ['xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation' => 'urn:magento:framework:ObjectManager/etc/config.xsd']);
            if ($configElement === null) return false;
            $dom->appendChild($configElement);
        }

        // <type name="Magento\Framework\Console\CommandListInterface">

        /** @var \DOMElement $typeElement */
        $typeElement = $configElement->getElementsByTagName('type')->item(0);
        if (!$typeElement) {
            $typeElement = $this->xml->createElement($dom, 'type', attributes: ['name' => 'Magento\Framework\Console\CommandListInterface']); // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
            if ($typeElement === null) return false;
            $configElement->appendChild($typeElement);
        }

        // <arguments>

        /** @var \DOMElement $argumentsElement */
        $argumentsElement = $typeElement->getElementsByTagName('arguments')->item(0);
        if (!$argumentsElement) {
            $argumentsElement = $this->xml->createElement($dom, 'arguments');
            if ($argumentsElement === null) return false;
            $typeElement->appendChild($argumentsElement);
        }

        // <argument name="commands" xsi:type="array">

        $argumentElements = $argumentsElement->getElementsByTagName('argument');
        $argumentElement = null;
        /** @var \DOMElement $element */
        foreach ($argumentElements as $element) {
            if ($element->getAttribute('name') === 'commands') {
                $argumentElement = $element;
            }
        }

        /** @var \DOMElement $argumentElement */
        if (!$argumentElement) {
            $argumentElement = $this->xml->createElement($dom, 'argument', attributes: ['name' => 'commands', 'xsi:type' => 'array']);
            if ($argumentElement === null) return false;
            $argumentsElement->appendChild($argumentElement);
        }

        // <item name="MakeBlock" xsi:type="object">MasterZydra\GenCli\Console\Command\MakeBlock</item>

        $namespacePath = $this->module->vendor() . '\\' . $this->module->module() . '\\Console\\Command\\' . $this->name;
        $itemElement = $this->xml->createElement($dom, 'item', $namespacePath, ['name' => $this->name,'xsi:type' => 'object']);
        if ($itemElement === null) return false;
        $argumentElement->appendChild($itemElement);

        return $this->xml->save($dom, $this->diXmlPath);
    }
}
