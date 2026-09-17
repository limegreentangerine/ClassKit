<?php

namespace ClassKit\Page;

use Page;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Localization\Translator\Adapter\Laminas\TranslatorAdapter;

trait TranslationAdaptorTrait
{
    /**
     * Gets Pages locale string for ajax powered translation adaptor
     *
     * @param int $id - Concrete Page ID
     *
     * @return string Language locale string
    */
    protected function getPageLocale($id): string
    {
        $c = Page::getById($id); // the page

        $ml = Section::getList();
        foreach ($ml as $m) {
            $tid = $m->getTranslatedPageID($c);
            if ($tid == $id) {
                return $m->getLocale();
            }
        }

        // default to english and uk
        return 'en_GB';
    }
    /**
     * Get Translation adaptor as t() and tc() are not available through Ajax
     *
     * @param int $cID - Concrete Page ID
     *
     * @return object TranslatorAdapter
     */
    public function getTranslationAdaptor($cID = false): TranslatorAdapter
    {
        $currentPage = ($cID) ? Page::getByID($cID) : Page::getByID(1);
        $lang = $this->getPageLocale($currentPage->getCollectionID());
        $loc = Localization::getInstance();
        $loc->setLocale($lang);
        return $loc->getActiveTranslatorAdapter();
    }
}
