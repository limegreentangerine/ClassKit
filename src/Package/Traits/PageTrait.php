<?php

namespace ClassKit\Package\Traits;

use Page;
use PageType;
use SinglePage;
use PageTemplate;
use Concrete\Core\Entity\Package;
use Concrete\Core\Page\Type\PublishTarget\Type\Type as PublishTargetType;

trait PageTrait
{
    /**
     * Add a Specific Page
     *
     * @param string|int        $pathOrCID
     * @param string            $name
     * @param string            $description
     * @param string            $type
     * @param string            $template
     * @param string|int|object $parent
     * @param Package           $pkg
     * @param string            $handle
     *
     * @return object Page
     */
    protected function addPage(
        $pathOrCID,
        string $name,
        string $description,
        string $type,
        string $template,
        $parent,
        Package $pkg,
        ?string $handle = null,
    ): Page {
        //Get Page if it's already created
        $page = is_int($pathOrCID) ? Page::getByID($pathOrCID) : Page::getByPath($pathOrCID);

        if ($page->isError() && $page->getError() == COLLECTION_NOT_FOUND) {
            //Get Page Type and Templates from their handles
            $pageType = PageType::getByHandle($type);
            $pageTemplate = PageTemplate::getByHandle($template);

            $parent = is_object($parent)
                ? $parent
                : (is_int($parent)
                    ? Page::getByID($parent)
                    : Page::getByPath($parent));

            //Get package
            $pkgID = $pkg->getPackageID();

            //Create Page
            $page = $parent->add($pageType, [
                'cName' => $name,
                'cHandle' => $handle,
                'cDescription' => $description,
                'pkgID' => $pkgID,
            ], $pageTemplate);
        }

        return $page;
    }

    /**
     * Adds a Page Type with an All Publish Target (can publish anywhere)
     *
     * @param string  $typeHandle
     * @param string  $typeName
     * @param string  $defaultTemplateHandle
     * @param string  $allowedTemplates      (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array   $templateArray         Array or Iterator of selected templates, see `$allowedTemplates`
     * @param Package $pkg
     * @param int     $startingPointCID
     * @param int     $selectorFormFactor
     *
     * @return object PageType
     */
    protected function addPageTypeWithAllPublishTarget(
        string $typeHandle,
        string $typeName,
        string $defaultTemplateHandle,
        string $allowedTemplates,
        array $templateArray,
        Package $pkg,
        int $startingPointCID = 0,
        int $selectorFormFactor = 0,
    ): PageType {
        $pt = PageType::getByHandle($typeHandle);
        if (!is_object($pt)) {
            $pto = $this->addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg);
            $pt = $this->setAllPublishTarget($pto, $startingPointCID, $selectorFormFactor);
        }

        return $pt;
    }

    /**
     * Add a Page Type with a Page Type Publish Target
     *
     * @param string  $typeHandle
     * @param string  $typeName
     * @param string  $defaultTemplateHandle
     * @param string  $allowedTemplates      (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array   $templateArray         Array or Iterator of selected templates, see `$allowedTemplates`
     * @param int     $parentPageTypeID
     * @param Package $pkg
     * @param int     $startingPointCID
     * @param int     $selectorFormFactor
     *
     * @return object PageType
     */
    protected function addPageTypeWithPageTypePublishTarget(
        string $typeHandle,
        string $typeName,
        string $defaultTemplateHandle,
        string $allowedTemplates,
        array $templateArray,
        int $parentPageTypeID,
        Package $pkg,
        int $startingPointCID = 0,
        int $selectorFormFactor = 0,
    ): PageType {
        $pt = PageType::getByHandle($typeHandle);
        if (!is_object($pt)) {
            $pto = $this->addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg);
            $pt = $this->setPageTypePublishTarget($pto, $parentPageTypeID, $startingPointCID, $selectorFormFactor);
        }

        return $pt;
    }

    /**
     * Add a Page Type with a Parent Page Publish Target
     *
     * @param string  $typeHandle
     * @param string  $typeName
     * @param string  $defaultTemplateHandle
     * @param string  $allowedTemplates      (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array   $templateArray         Array or Iterator of selected templates, see `$allowedTemplates`
     * @param int     $parentPageCID
     * @param Package $pkg
     *
     * @return object PageType
     */
    protected function addPageTypeWithParentPagePublishTarget(
        string $typeHandle,
        string $typeName,
        string $defaultTemplateHandle,
        string $allowedTemplates,
        array $templateArray,
        int $parentPageCID,
        Package $pkg,
    ): PageType {
        $pt = PageType::getByHandle($typeHandle);
        if (!is_object($pt)) {
            $pto = $this->addPageType($typeHandle, $typeName, $defaultTemplateHandle, $allowedTemplates, $templateArray, $pkg);
            $pt = $this->setParentPagePublishTarget($pto, $parentPageCID);
        }

        return $pt;
    }

    /**
     * Add New Page Type
     *
     * @param string  $typeHandle
     * @param string  $typeName
     * @param string  $defaultTemplateHandle
     * @param string  $allowedTemplates      (A|C|X) A for all, C for selected only, X for non-selected only
     * @param array   $templateArray         Array or Iterator of selected templates, see `$allowedTemplates`
     * @param Package $pkg
     *
     * @return object PageType
     */
    protected function addPageType(
        string $typeHandle,
        string $typeName,
        string $defaultTemplateHandle,
        string $allowedTemplates,
        array $templateArray,
        Package $pkg,
    ): PageType {
        //Get required objects (these can be handles after 8)
        $defaultTemplate = PageTemplate::getByHandle($defaultTemplateHandle);
        $allowedTemplateArray = [];
        foreach ($templateArray as $handle) {
            $allowedTemplateArray[] = PageTemplate::getByHandle($handle);
        }

        $data = [
            'handle' => $typeHandle,
            'name' => $typeName,
            'defaultTemplate' => $defaultTemplate,
            'allowedTemplates' => $allowedTemplates,
            'templates' => $allowedTemplateArray,
        ];

        $pt = PageType::getByHandle($typeHandle);
        if (is_object($pt)) {
            $pt->update($data);
            return $pt;
        }

        return PageType::add($data, $pkg);
    }

    /**
     * Set All Pages Publish Target for Page Type
     *
     * @param object $pageTypeObject     PageType
     * @param int    $startingPointCID
     * @param int    $selectorFormFactor
     *
     * @return object PageType
     */
    protected function setAllPublishTarget(
        PageType $pageTypeObject,
        int $startingPointCID = 0,
        int $selectorFormFactor = 0,
    ): PageType {
        $allTarget = PublishTargetType::getByHandle('all');
        $configuredTarget = $allTarget->configurePageTypePublishTarget(
            $pageTypeObject,
            [
                'selectorFormFactorAll' => $selectorFormFactor, // this is the form factor of the page selector. null or false is the standard sitemap popup. 1 or true would be the in page sitemap
                'startingPointPageIDall' => ($startingPointCID), // If you only want this available below a certain explicit page, but anywhere nested under that page, set this page id. null or false sets this to anywhere
            ],
        );
        $pageTypeObject->setConfiguredPageTypePublishTargetObject($configuredTarget);

        return $pageTypeObject;
    }

    /**
     * Set Page Type Publish Target for Page Type
     *
     * @param object $pageTypeObject     PageType
     * @param int    $parentPageTypeID
     * @param int    $startingPointCID
     * @param int    $selectorFormFactor
     *
     * @return object PageType
     */
    protected function setPageTypePublishTarget(
        PageType $pageTypeObject,
        int $parentPageTypeID,
        int $startingPointCID = 0,
        int $selectorFormFactor = 0,
    ): PageType {
        $typeTarget = PublishTargetType::getByHandle('page_type');
        $configuredTypeTarget = $typeTarget->configurePageTypePublishTarget(
            $pageTypeObject, //the one being set up, NOT the target one
            [
                'ptID' => $parentPageTypeID,
                'startingPointPageIDPageType' => $startingPointCID, // this is the form factor of the page selector. null or false is the standard sitemap popup. 1 or true would be the in page sitemap
                'selectorFormFactorPageType' => $selectorFormFactor, // If you only want this available below a certain explicit page, but anywhere nested under that page, set this page id. null or false sets this to anywhere
            ],
        );
        $pageTypeObject->setConfiguredPageTypePublishTargetObject($configuredTypeTarget);

        return $pageTypeObject;
    }

    /**
     * Set Parent Page Publish Target for Page Type
     *
     * @param object $pageTypeObject PageType
     * @param int    $parentPageCID
     *
     * @return object PageType
     */
    protected function setParentPagePublishTarget(PageType $pageTypeObject, int $parentPageCID): PageType
    {
        $parentTarget = PublishTargetType::getByHandle('parent_page');
        $configuredParentTarget = $parentTarget->configurePageTypePublishTarget(
            $pageTypeObject,
            [
                'CParentID' => $parentPageCID,
            ],
        );
        $pageTypeObject->setConfiguredPageTypePublishTargetObject($configuredParentTarget);

        return $pageTypeObject;
    }

    /**
     * Add Single Page
     *
     * @param string  $path
     * @param Package $pkg
     * @param string  $name
     * @param string  $description
     *
     * @return object SinglePage
     */
    protected function addSinglePage(string $path, $pkg, string $name = '', string $description = ''): Page
    {
        //Install single page
        $sp = Page::getByPath($path);

        if ($sp->isError() && $sp->getError() == COLLECTION_NOT_FOUND) {
            $sp = SinglePage::add($path, $pkg);
        }

        //Set name and description
        if (!empty($name) || !empty($description)) {
            $data = [];
            if (!empty($name)) {
                $data['cName'] = $name;
            }
            if (!empty($description)) {
                $data['cDescription'] = $description;
            }
            $sp->update($data);
        }

        return $sp;
    }

    /**
     * Add a Page Template
     *
     * @param string  $handle
     * @param string  $name
     * @param Package $pkg
     * @param string  $icon
     *
     * @return object PageTemplate
     */
    protected function addPageTemplate(string $handle, string $name, Package $pkg, string $icon = 'landing.png'): PageTemplate
    {
        $template = PageTemplate::getByHandle($handle);
        if (!is_object($template)) {
            $template = PageTemplate::add($handle, $name, $icon, $pkg);
        }

        return $template;
    }
}
