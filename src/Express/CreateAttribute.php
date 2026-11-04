<?php

namespace ClassKit\Express;

use Concrete\Core\Entity\Attribute\Key\Settings\Settings;

final readonly class CreateAttribute
{
    public function __construct(
        public string $type_handle,
        public string $name,
        public string $handle,
        public ?Settings $settings = null,
        public bool $akIsSearchable = true,
        public bool $akIsSearchableIndexed = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type_handle: $data['type_handle'],
            name: $data['name'],
            handle: $data['handle'],
            settings: $data['settings'],
            akIsSearchable: $data['akIsSearchable'] ?? true,
            akIsSearchableIndexed: $data['akIsSearchableIndexed'] ?? false,
        );
    }
}
