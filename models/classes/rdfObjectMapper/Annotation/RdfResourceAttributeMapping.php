<?php

namespace oat\tao\model\RdfObjectMapper\Annotation;

//#[\Attribute(\Attribute::TARGET_PROPERTY)]
/**
 *@Annotation
 */
class RdfResourceAttributeMapping
{
    public /*int*/ $type = 0;

    //public function __construct(int $attributeType)
    /*public function __construct($data)
    {
        //$this->attributeType = $attributeType;

        //echo "Called RdfResourceAttributeMapping ctor with ".var_export($data,true);
    }*/

    public function hydrate(
        \ReflectionProperty $property,
        \core_kernel_classes_Resource $src,
        object &$targetObject
    ): void
    {
        echo __CLASS__ .
            " should map a (direct) value from".
            " the resource class to the property<br/>\n";

        $value = null;
        switch ($this->type)
        {
            case RdfResourceAttributeType::LABEL:
                $value = $src->getLabel();
                break;
            case RdfResourceAttributeType::COMMENT:
                $value = $src->getComment();
                break;
            case RdfResourceAttributeType::URI:
                $value = $src->getUri();
                break;
            default:
                throw new \LogicException(
                    "Unknown ".__CLASS__."::type value: ".
                    $this->type
                );
        }

        echo "Mapping value {$value} into {$property->getName()}<br/>";

        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            // Not needed starting PHP 8.1 (it has become a no-op since then)
            $property->setAccessible(true);
        }

        $property->setValue($targetObject, $value);
    }
}
