<?php

namespace oat\tao\model\RdfObjectMapper\Annotation;

//#[\Attribute(\Attribute::TARGET_PROPERTY)]
/**
 *@Annotation
 */
class RdfAttributeMapping
{
    public /*string*/ $propertyUri;
    public /*string*/ $attributeType = 'resource';
    public /*string*/ $mappedField = null;

    // the commented out constructors are in case we use PHP 8 annotations
    /*public function __construct(
        string $propertyUri,
        string $attributeType = 'resource',
        string $mappedField  = ''
    )

    {
        $this->propertyUri   = $propertyUri;
        $this->attributeType = $attributeType;
        $this->mappedField   = $mappedField;
    }*/

    /*public function __construct() //($data, $b, $c)
    {
        //$this->attributeType = $attributeType;

        //echo "Called RdfAttributeMapping ctor with ".var_export($data,true);
    }*/

    public function hydrate(
        \ReflectionProperty $property,
        \core_kernel_classes_Resource $src,
        object &$targetObject
    ): void
    {
        echo __CLASS__ . " should map a value to the property<br/>\n";

        $values = $src->getPropertyValues(
            new \core_kernel_classes_Property($this->propertyUri)
        );

        if(count($values) == 0) {
            echo "No value to map";
            return;
        }

        if(count($values) > 1) {
            echo "too many values to map";
            return;
        }

        if(count($values) == 1) {
            $value = current($values);
            echo "Mapping value {$value} into {$property->getName()}<br/>";

            $property->setAccessible(true);
            $property->setValue($targetObject, $value);
        }
    }
}
