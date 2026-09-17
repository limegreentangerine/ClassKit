<?php
namespace ClassKit\Search\Result\Item;

use Concrete\Core\Search\Result\Result;
use Concrete\Core\Search\Column\Set;

trait ItemTrait
{
    /**
     * @var object
     */
    protected object $entity;

    /**
     * @var Result $result
     * @var Set $columns
     * @var object $item
     */
    public function __construct(Result $result, Set $columns, object $item)
    {
        parent::__construct($result, $columns, $item);
        $this->entity = $item;
    }

    abstract public function getViewUrl();

    /**
     * @return int
     */
    public function getID()
    {
        return $this->entity->getID();
    }
}
