<?php

namespace ClassKit\Page;

use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Attribute\Controller as AttributeController;

/**
 * Extends the core Concrete page with package-specific attribute helpers.
 */
class Page extends \Concrete\Core\Page\Page
{
    /**
     * Returns the attribute controller for a requested page attribute.
     *
     * @param string $handle The attribute handle to resolve.
     *
     * @return AttributeController|null The matching attribute controller or null when unavailable.
     */
    public function getAttributeController(string $handle): ?AttributeController
    {
        $attrKey = CollectionKey::getByHandle($handle);

        if (is_object($attrKey) && is_object($this) && !$this->isError()) {
            $attributeValue = $this->getAttributeValueObject($attrKey);

            if ($attributeValue) {
                $controller = $attributeValue->getController();
                return $controller;
            }
        }

        return null;
    }
}
