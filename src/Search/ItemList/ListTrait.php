<?php
namespace ClassKit\Search\ItemList;

/**
 * @property \Doctrine\DBAL\Query\QueryBuilder $query
 */
trait ListTrait
{
    /**
     * Filter by
     *
     * @var string $field
     * @var mixed $value
     * @var string $comparison
     *
     * @return void
     */
    public function filterBy(string $field, mixed $value, string $comparison = '='): void
    {
        // Normalize IN and NOT IN values to arrays
        if (in_array(strtoupper($comparison), ['IN', 'NOT IN'], true)) {
            if (!is_array($value)) {
                $value = array_map('trim', explode(',', (string) $value));
            }
        }

        $expression = match(strtolower($comparison)) {
            '!=', '<>'  => $this->query->expr()->neq($field, $this->query->createNamedParameter($value)),
            '<'         => $this->query->expr()->lt($field, $this->query->createNamedParameter($value)),
            '<='        => $this->query->expr()->lte($field, $this->query->createNamedParameter($value)),
            '>'         => $this->query->expr()->gt($field, $this->query->createNamedParameter($value)),
            '>='        => $this->query->expr()->gte($field, $this->query->createNamedParameter($value)),
            'like'      => $this->query->expr()->like($field, $this->query->createNamedParameter('%' . addcslashes($value, '%_\\') . '%')),
            'not like'  => $this->query->expr()->notLike($field, $this->query->createNamedParameter('%' . addcslashes($value, '%_\\') . '%')),
            'in'        => $this->query->expr()->in($field, $this->query->createNamedParameter($value, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)),
            'not in'    => $this->query->expr()->notIn($field, $this->query->createNamedParameter($value, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)),
            default     => $this->query->expr()->eq($field, $this->query->createNamedParameter($value)),
        };

        $this->query->andWhere($expression);
    }
}
