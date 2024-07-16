<?php declare(strict_types=1);
/**
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @copyright BibLibre, 2016
 * @copyright Daniel Berthereau, 2014-2023
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace OaiPmhRepository\OaiPmh;

use DOMElement;

/**
 * Parent class for all XML-generating classes.
 */
class AbstractXmlGenerator
{
    const XML_SCHEMA_NAMESPACE_URI = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * Creates a new XML element with the specified children.
     *
     * Creates a parent element with the given name, with children with names
     * and values as given.  Adds the resulting element as a child of the given
     * element
     *
     * @param DomElement $parent   Existing parent of all the new nodes
     * @param string     $name     Name of the new parent element
     * @param array      $children Child names and values, as name => value
     * @return DomElement The new tree of elements
     */
    protected function createElementWithChildren(DOMElement $parent, $name, $children)
    {
        $document = $parent->ownerDocument;
        $newElement = $document->createElement($name);
        foreach ($children as $tag => $value) {
            if (is_array($value)) {
                $this->createElementWithChildren($newElement, $tag, $value);
            } else {
                $element = $document->createElement($tag);
                $value = $this->stripInvalidXmlChars((string) $value);
                $element->appendChild($document->createTextNode($value));
                $newElement->appendChild($element);
            }
        }
        $parent->appendChild($newElement);

        return $newElement;
    }

    /**
     * Creates a parent element with the given name, with text as given.
     *
     * Adds the resulting element as a child of the given parent node.
     *
     * @param DomElement $parent Existing parent of all the new nodes
     * @param string     $name   Name of the new parent element
     * @param string     $text   Text of the new element
     * @param array      $attributes List of attributes
     * @return DomElement The new element
     */
    protected function appendNewElement(DOMElement $parent, $name, $text = null, array $attributes = [])
    {
        $document = $parent->ownerDocument;
        $newElement = $document->createElement($name);
        // Use a TextNode, causes escaping of input text
        if ($text) {
            $text = $this->stripInvalidXmlChars((string) $text);
            $text = $document->createTextNode($text);
            $newElement->appendChild($text);
        }
        foreach ($attributes as $name => $attribute) {
            $newElement->setAttribute($name, $this->stripInvalidXmlChars($attribute));
        }
        $parent->appendChild($newElement);

        return $newElement;
    }

    protected function stripInvalidXmlChars(string $string): string
    {
        return preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', '', $string);
    }
}
