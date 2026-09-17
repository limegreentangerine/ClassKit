<?php

namespace ClassKit\Entity\Core;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    public function __construct() {}

    public function getID(): int
    {
        return $this->id;
    }

    /**
     * getByID
     *
     * @var int $id
     *
     * @return object|bool
     */
    public static function getByID(int $id): mixed
    {
        $em = \ORM::entityManager();
        $repository = $em->getRepository(get_called_class());

        $entity = $repository->find($id);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
    }

    /**
     * getByColumnAndValue
     *
     * @var string $columnName
     * @var string $value
     *
     * @return object|bool
     */
    public static function getByColumnAndValue(string $columnName, string $value): mixed
    {
        $em = \ORM::entityManager();
        $repository = $em->getRepository(get_called_class());

        $entity = $repository->findOneBy([
            $columnName => $value,
        ]);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
    }
}
