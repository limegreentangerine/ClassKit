<?php

namespace ClassKit\Area;

use View;
use Punic\Data;
use Localization;
use Punic\Language;
use Concrete\Core\Page\View\PageView;
use Concrete\Core\Area\GlobalArea as CoreGlobalArea;
use Concrete\Core\Multilingual\Page\Section\Section as MultilingualSection;

/**
 * Extends the core global area with a locale-aware public handle.
 */
class GlobalArea extends CoreGlobalArea
{
    /**
     * Create a localized global area handle for the active locale.
     *
     * @param string $arHandle The base handle to localize.
     */
    public function __construct(string $arHandle)
    {
        $ms = MultilingualSection::getCurrentSection();
        $locale = is_object($ms) ? $ms->getLocale() : Localization::activeLocale();
        $fallbackLocale = Data::getFallbackLocale();

        if ($locale != $fallbackLocale) {
            $locName = Language::getName($locale, $fallbackLocale);
            $arHandle = $arHandle . ' ' . $locName;
        } else {
            $locName = Language::getName('en_GB');
            $arHandle = $arHandle . ' ' . $locName;
        }

        $this->arHandle = $arHandle;

        $v = View::getRequestInstance();
        if ($v instanceof PageView && $v->isEditingDisabled()) {
            $this->disableControls();
        }
    }
}
