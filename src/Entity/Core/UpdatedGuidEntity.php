<?php

namespace ClassKit\Entity\Core;

use ClassKit\Entity\Core\GuidEntity;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      indexes={
 *          @ORM\Index(name="date_created", columns={"date_created"}),
 *          @ORM\Index(name="date_updated", columns={"date_updated"})
 *      }
 * )
 */
abstract class UpdatedGuidEntity extends GuidEntity
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTimeImmutable $date_created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTimeImmutable $date_updated;

    /**
     * Executes __construct.
     */
    public function __construct()
    {
        $now = new DateTimeImmutable();

        $this->date_created = $now;
        $this->date_updated = $now;

        parent::__construct();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime(): void
    {
        $this->setDateUpdated(new DateTimeImmutable());
    }
    /**
     * Executes setDateCreated.
     */
    public function setDateCreated(DateTimeImmutable $value): void
    {
        $this->date_created = $value;
    }

    /**
     * Executes getDateCreated.
     */
    public function getDateCreated(): DateTimeImmutable
    {
        return $this->date_created;
    }

    /**
     * Executes getDateCreatedString.
     */
    public function getDateCreatedString(string $format = 'd/m/Y H:i'): string
    {
        return $this->date_created->format($format);
    }

    /**
     * Executes getDateUpdated.
     */
    public function getDateUpdated(): DateTimeImmutable
    {
        return $this->date_updated;
    }

    /**
     * Executes getDateUpdatedString.
     */
    public function getDateUpdatedString(string $format = 'd/m/Y H:i'): string
    {
        return $this->date_updated->format($format);
    }

    /**
     * Executes setDateUpdated.
     */
    public function setDateUpdated(DateTimeImmutable $value): void
    {
        $this->date_updated = $value;
    }
}
