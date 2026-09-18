<?php

namespace ClassKit\Package\Traits;

use ORM;
use Concrete\Core\Entity\Package;
use Concrete\Core\Express\EntryList;
use Concrete\Core\Support\Facade\Express;

/**
 * Trait ExpressTrait.
 */
trait ExpressTrait
{
    /**
     * Adds Express Object, or finds existing one
     *
     * If Express Object is not set, build express object with given parameters
     *
     * @param  string  $handle
     * @param  string  $plural
     * @param  string  $name
     * @param  Package $pkg
     * @return Express
     */
    protected function addExpressObject(string $handle, string $plural, string $name, Package $pkg): Express
    {
        return is_object(Express::getObjectByHandle($handle)) ? Express::getObjectByHandle($handle) : Express::buildObject($handle, $plural, $name, $pkg);
    }

    /**
     * Some forms are just standalone and made with attributes, so this is a quick method for more quickly generating these forms
     *
     * @param  object  $expressObject
     * @param  array   $fieldArray    Multidimensional array, such as array('FieldsetName'=>array('attribute_handle1', 'attributehandle2, ...))
     * @param  string  $formName
     * @return Express
     */
    protected function createExpressAttributeForm(Express $expressObject, array $fieldArray, string $formName = 'Form'): Express
    {
        if (is_object($expressObject) && is_array($fieldArray)) {
            $form = $expressObject->buildForm($formName);

            foreach ($fieldArray as $fieldsetName => $fields) {
                $set = $form->addFieldSet($fieldsetName);
                foreach ($fields as $attribute) {
                    $set->addAttributeKeyControl($attribute);
                }
            }
            $form->save();
        }

        return $expressObject;
    }

    /**
     * More complicated forms have a few extra parameters to account for, but can also be automated a bit
     *
     * @param  object  $expressObject
     * @param  array   $fieldArray    Multidimensional array, such as array('FieldsetName'=>array('attribute_handle1'=>'attribute", 'text_control'=>'text', 'association_name'=>'association'))
     * @param  string  $formName
     * @param  bool    $set_default
     * @return Express
     */
    protected function createExpressForm(Express $expressObject, array $fieldArray, string $formName = 'Form', bool $set_default = true): Express
    {
        if (is_object($expressObject) && is_array($fieldArray)) {
            $form = $expressObject->buildForm($formName);

            foreach ($fieldArray as $fieldsetName => $fields) {
                $set = $form->addFieldset($fieldsetName);
                foreach ($fields as $name => $type) {
                    switch ($type) {
                        case 'attribute':
                            $set->addAttributeKeyControl($name);
                            break;
                        case 'association':
                            $set->addAssociationControl($name);
                            break;
                        case 'text':
                            $set->addTextControl('', $name); //skips headline for ease
                            break;
                        default:
                            break;
                    }
                }
            }
            $form = $form->save();
            if ($set_default == true) {
                $expressObject->setDefaultViewForm($form);
                $expressObject->setDefaultEditForm($form);
            }
        }

        return $expressObject;
    }

    /**
     * Does Express Entity Search
     *
     * @param  string $entity_handle
     * @param  string $search_attribute_handle
     * @param  string $search_value
     * @param  string $comparison
     * @return array
     */
    protected function getExpressEntries(string $entity_handle, string $search_attribute_handle, string $search_value, string $comparison = '='): array
    {
        $entity = Express::getObjectByHandle($entity_handle);
        $list = new EntryList($entity);
        $list->filterByAttribute($search_attribute_handle, $search_value, $comparison);
        return $list->getResults();
    }

    /**
     * Sets default Edit and View form for Express - Assumes entity is already set, not a builder
     *
     * @param object $entity
     * @param object $form
     */
    protected function setDefaultForms($entity, $form): void
    {
        $entity->setDefaultViewForm($form);
        $entity->setDefaultEditForm($form);
        $entityManager = ORM::entityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }
}
