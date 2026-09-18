<?php

namespace ClassKit\Package\Traits;

use Core;
use Concrete\Core\Entity\Package;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Attribute\Set as AttributeSet;
use Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Core\Page\Type\Composer\Control\CollectionAttributeControl;

/**
 * Trait AttributeTrait.
 */
trait AttributeTrait
{
    /**
     * Add Attribute Type
     *
     * @param string  $handle
     * @param string  $name
     * @param Package $pkg
     * @param array   $att_sets
     *
     * @return AttributeType
     */
    protected function addAttributeType(
        string $handle,
        string $name,
        Package $pkg,
        array $att_sets = ['collection'],
    ) {
        $att_type = AttributeType::getByHandle($handle);

        if (!is_object($att_type)) {
            $att_type = AttributeType::add($handle, t($name), $pkg);
        }

        // Assign to the Attribute Set: Collections
        foreach ($att_sets as $as_handle) {
            $col = Category::getByHandle($as_handle);
            $col->associateAttributeKeyType($att_type);
        }

        return $att_type;
    }

    /**
     * Add Attribute Set
     *
     * @param string  $categoryHandle
     * @param string  $setHandle
     * @param string  $setName
     * @param Package $pkg
     *
     * @return AttributeSet
     */
    protected function addAttributeSet(
        string $categoryHandle,
        string $setHandle,
        string $setName,
        Package $pkg,
    ) {
        $pakc = Category::getByHandle($categoryHandle);
        $pakc->setAllowAttributeSets(Category::ASET_ALLOW_MULTIPLE);

        //get or set Attribute Set
        $att_set = AttributeSet::getByHandle($setHandle);
        if (!is_object($att_set)) {
            $att_set = $pakc->addSet($setHandle, t($setName), $pkg);
        }

        return $att_set;
    }

    /**
     * Add Custom Attribute Key
     *
     * @param string  $handle
     * @param string  $name
     * @param string  $type
     * @param mixed   $categoryKeyObject
     * @param ?object $attributeSetObject
     * @param Package $pkg
     * @param bool    $selectAllowOtherValues
     *
     * @return object Attribute Object
     */
    protected function addAttribute(
        string $handle,
        string $name,
        string $type,
        mixed $categoryKeyObject,
        ?object $attributeSetObject,
        Package $pkg,
        bool $selectAllowOtherValues = true,
    ) {
        $attr = $categoryKeyObject::getByHandle($handle);
        if (!is_object($attr)) {
            $info = [
                'akHandle' => $handle,
                'akName' => $name,
                'akIsSearchable' => true,
            ];
            $att_type = AttributeType::getByHandle($type);
            $attr = $categoryKeyObject::add($att_type, $info, $pkg);

            // Add attribute to attribute set
            if ($attributeSetObject) {
                // Deprecated? Not working?
                //$attr->setAttributeSet($attributeSetObject);

                // Try this:
                $attributeSetObject->addKey($attr);

                $entityManager = Core::make(EntityManagerInterface::class);
                $entityManager->persist($attributeSetObject);
                $entityManager->flush();
            }

            if ($type == 'select' && $selectAllowOtherValues == true) {
                $attr->getController()->setAllowOtherValues();
            }
        }

        return $attr;
    }

    /**
     * Adds an Attribute Form Control
     *
     * @param string $attributeHandle
     * @param object $layoutSet
     * @param string $customName
     * @param string $customDescription
     *
     * @return CollectionAttributeControl
     */
    protected function addAttributeFormControl(
        string $attributeHandle,
        object $layoutSet,
        ?string $customName = null,
        ?string $customDescription = null,
    ) {
        $fc = new CollectionAttributeControl();
        $aID = CollectionKey::getByHandle($attributeHandle)->getAttributeKeyID();
        $fc->setAttributeKeyId($aID);
        $formLayoutSet = $fc->addToPageTypeComposerFormLayoutSet($layoutSet);
        if (!empty($customName)) {
            $formLayoutSet->updateFormLayoutSetControlCustomLabel($customName);
        }
        if (!empty($customDescription)) {
            $formLayoutSet->updateFormLayoutSetControlDescription($customDescription);
        }

        return $fc;
    }

    /**
     * Add a select attribute with options
     *
     * @param string  $handle
     * @param string  $name
     * @param array   $optionList
     * @param mixed   $categoryKeyObject
     * @param mixed   $attributeSetObject
     * @param Package $pkg
     * @param bool    $allowOtherValues
     * @param bool    $hideNoneOption
     * @param bool    $allowMultipleValues
     *
     * @return object The attribute object
     */
    protected function addSelectAttribute(
        string $handle,
        string $name,
        array $optionList,
        mixed $categoryKeyObject,
        mixed $attributeSetObject,
        Package $pkg,
        bool $allowOtherValues = false,
        bool $hideNoneOption = true,
        bool $allowMultipleValues = false,
    ) {
        $attr = $categoryKeyObject::getByHandle($handle);
        if (!is_object($attr)) {
            $info = [
                'akHandle' => $handle,
                'akName' => $name,
                'akIsSearchable' => true,
                'akSelectAllowMultipleValues' => $allowMultipleValues,
                'akSelectAllowOtherValues' => $allowOtherValues,
                'akHideNoneOption' => $hideNoneOption,
            ];

            $att_type = AttributeType::getByHandle('select');
            $attr = $categoryKeyObject::add($att_type, $info, $pkg);

            if ($attributeSetObject) {
                $attributeSetObject->addKey($attr);
                $entityManager = Core::make(EntityManagerInterface::class);
                $entityManager->persist($attributeSetObject);
                $entityManager->flush();
            }

            // Use setOptions instead of manual loop
            $controller = $attr->getController();
            $controller->setOptions($optionList);
            $controller->saveKey($info);
        }
        return $attr;
    }
}
