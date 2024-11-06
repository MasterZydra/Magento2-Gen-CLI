<?php

declare(strict_types=1);

// phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
// phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace MasterZydra\GenCli\Helper;

class Xml extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @inheritdoc */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        private \MasterZydra\GenCli\Helper\File $file,
    ) {
        parent::__construct($context);
    }

    /** Read given XML file */
    public function read(string $path): ?\DOMDocument
    {
        $document = new \DomDocument();
        $document->formatOutput = true;
        $document->preserveWhiteSpace = false;

        if ($document->load($path)) {
            return $document;
        }
        return null;
    }

    /** Create a new DOM element */
    public function createElement(
        \DOMDocument $document,
        string $localName,
        string $value = '',
        array $attributes = [],
    ): ?\DOMElement {
        $element = $document->createElement($localName, $value);
        if ($element === false) {
            return null;
        }
        foreach ($attributes as $qualifiedName => $value) {
            if ($element->setAttribute($qualifiedName, $value) === false) {
                return null;
            }
        }
        return $element;
    }

    /** Write XML to given file */
    public function save(\DOMDocument $document, string $path): bool
    {
        $xml = $document->saveXML();
        if ($xml === false) {
            return false;
        }
        return $this->file->write($path, $xml) > 0;
    }
}
