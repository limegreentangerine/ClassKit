<?php

namespace ClassKit\Search\Result\Item;

use Concrete\Core\Search\Column\Set;
use Concrete\Core\Search\Result\Result;

/**
 * Trait ItemTrait.
 */
trait ItemTrait
{
    /**
     * @var object
     */
    protected object $entity;

    /**
     * @var Result $result
     * @var Set    $columns
     * @var object $item
     */
    public function __construct(Result $result, Set $columns, object $item)
    {
        parent::__construct($result, $columns, $item);
        $this->entity = $item;
    }

    /**
     * Executes getViewUrl.
     */
    abstract public function getViewUrl();

    /**
     * @return int
     */
    public function getID()
    {
        return $this->entity->getID();
    }
}
