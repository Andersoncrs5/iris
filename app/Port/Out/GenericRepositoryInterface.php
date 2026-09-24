<?php

namespace App\Port\Out;

/**
 * @template T of object
 * @template ID of int|string
 */
interface GenericRepositoryInterface
{
    /**
     * @param ID $id
     * @return T|null
     */
    public function findById(mixed $id): ?object;

    /**
     * @param ID $id
     * @return T|null
     */
    public function findByIdForUpdate(mixed $id): ?object;

    /**
     * @param ID $id
     */
    public function existsById(mixed $id): bool;

    /**
     * @param ID $id
     */
    public function deleteById(mixed $id): bool;

    /**
     * @param T $entity
     * @return T
     */
    public function save(object $entity): object;

    /**
     * @param array<T> $entities
     * @return array<T>
     */
    public function saveAll(array $entities): array;

    public function countAll(): int;

    public function deleteAll(): int;
}