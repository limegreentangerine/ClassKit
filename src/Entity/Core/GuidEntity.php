<?php

namespace ClassKit\Entity\Core;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class GuidEntity
{
    /**
     * @ORM\Id @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected string $id;

    /**
     * Executes __construct.
     */
    public function __construct() {}

    /**
     * Executes getID.
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * Executes getByID.
     */
    public static function getByID(string $id): mixed
    {
        $em = \ORM::entityManager();
        $repository = $em->getRepository(get_called_class());

        $entity = $repository->findOneBy(['id' => $id]);

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
