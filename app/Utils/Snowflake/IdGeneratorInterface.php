<?php

namespace App\Utils\Snowflake;

interface IdGeneratorInterface
{
    public function generate(): int;

    /**
     * @return list<int>
     */
    public function generateMany(int $quantity): array;

    public function getWorkerId(): int;

    public function isValid(int $id): bool;
}