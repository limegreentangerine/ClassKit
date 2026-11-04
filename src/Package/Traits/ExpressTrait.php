<?php

namespace ClassKit\Package\Traits;

use Core;
use Exception;
use Concrete\Core\Entity\Package;
use Concrete\Core\Express\EntryList;
use ClassKit\Express\CreateAttribute;
use Concrete\Core\Entity\Express\Form;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Entity\Express\FieldSet;
use Concrete\Core\Entity\Express\Entity as ExpressEntity;
use Concrete\Core\Entity\Express\Control\AssociationControl;
use Concrete\Core\Entity\Express\Control\AttributeKeyControl;

/**
 * Trait ExpressTrait.
 */
trait ExpressTrait
{
    /**
     * Build Express Object
     * - Builds an express object, sets its attributes and creates the default form
     *
     * @param string                      $handle
     * @param string                      $plural
     * @param string                      $name
     * @param array<int, CreateAttribute> $attrs
     * @param Package                     $pkg
     * @param string                      $formName            - Default: Form
     * @param string                      $fieldsetName        - Default: Details
     * @param ?callable                   $beforeForm          - Function to run before form creations, usually to link express entities together
     * @param array                       $associationControls
     *
     * Example:
     * $industry = $this->buildExpressObject('industry', 'industries', t('Industry'), $industryAttributes, $pkg);
     * $this->buildExpressObject(
     *    'partner', 'partners', t('Partner'), $partnerAttributes, $pkg,
     *      beforeForm: function ($builder) use ($industry) {
     *          $builder->buildAssociation()
     *              ->addManyToMany($industry, 'industries', 'partners')
     *              ->save();
     *      },
     *      associationControls: ['industries'],
     *  );
     *
     * @return ExpressEntity
     * @throws Exception
     */
    protected function buildExpressObject(
        string $handle,
        string $plural,
        string $name,
        array $attrs,
        Package $pkg,
        string $formName = 'Form',
        string $fieldsetName = 'Details',
        ?callable $beforeForm = null,
        array $associationControls = [],
    ): ExpressEntity {
        $entity = Express::getObjectByHandle($handle);
        if ($entity) {
            return $entity;
        }

        $builder = Express::buildObject($handle, $plural, $name, $pkg);

        foreach ($attrs as $attr) {
            $builder->addAttribute(
                $attr->type_handle,
                $attr->name,
                $attr->handle,
                $attr->settings,
                $attr->akIsSearchable,
                $attr->akIsSearchableIndexed,
            );
        }

        $entity = $builder->save();

        if ($beforeForm) {
            $beforeForm($builder);
        }

        $form = $builder->buildForm($formName);
        $fieldset = $form->addFieldset($fieldsetName);

        foreach ($attrs as $attr) {
            $fieldset->addAttributeKeyControl($attr->handle);
        }

        foreach ($associationControls as $targetProperty) {
            $fieldset->addAssociationControl($targetProperty);
        }

        $form = $form->save();

        $entity->setDefaultEditForm($form);
        $entity->setDefaultViewForm($form);

        $em = Core::make(EntityManagerInterface::class);
        $em->persist($entity);
        $em->flush();

        return $entity;
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
     *  Create Express Attribute Form
     *
     * @param ExpressEntity               $entity
     * @param array<int, CreateAttribute> $attrs
     * @param array<int, string>          $associations
     * @param string                      $formName     - Default: Form
     * @param string                      $fieldsetName - Default: Details
     * @param bool                        $setAsDefault
     *
     * @throws Exception
     */
    public function createExpressAttributeForm(
        ExpressEntity $entity,
        array $attrs = [],
        array $associations = [],
        string $formName = 'Form',
        string $fieldsetName = 'Details',
        bool $setAsDefault = false,
    ): void {
        if ($entity) {
            $form = new Form();
            $form->setEntity($entity);
            $form->setName($formName);

            $fieldset = new FieldSet();
            $fieldset->setForm($form);
            $fieldset->setTitle($fieldsetName);

            $controls = [];
            foreach ($attrs as $attr) {
                $control = new AttributeKeyControl();
                $control->setAttributeKey($attr->handle);
                $control->setFieldSet($fieldset);
                $controls[] = $control;
            }

            foreach ($associations as $targetProperty) {
                $control = new AssociationControl();
                $control->setAssociation($targetProperty);
                $control->setFieldSet($fieldset);
                $controls[] = $control;
            }

            $fieldset->setControls($controls);

            if ($setAsDefault) {
                $entity->setDefaultEditForm($form);
                $entity->setDefaultViewForm($form);
            }

            $em = Core::make(EntityManagerInterface::class);
            $em->persist($entity);
            $em->flush();
        }
    }
}
