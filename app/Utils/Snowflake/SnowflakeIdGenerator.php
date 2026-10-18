<?php

namespace App\Utils\Snowflake;

use Godruoyi\Snowflake\Snowflake;
use InvalidArgumentException;

class SnowflakeIdGenerator implements IdGeneratorInterface
{
    private const TIMESTAMP_SHIFT = 22;
    private const MAX_TIMESTAMP = (1 << 41) - 1;
    private const MAX_WORKER_ID = (1 << 5) - 1;

    private Snowflake $snowflake;

    public function __construct(
        private readonly int $workerId = 1
    ) {
        if ($workerId < 0 || $workerId > self::MAX_WORKER_ID) {
            throw new InvalidArgumentException(
                'Worker ID must be between 0 and ' . self::MAX_WORKER_ID . '.'
            );
        }

        $this->snowflake = new Snowflake(0, $workerId);
    }

    public function generate(): int
    {
        return (int) $this->snowflake->id();
    }

    /**
     * @return list<int>
     */
    public function generateMany(int $quantity): array
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException(
                'Quantity cannot be negative.'
            );
        }

        $ids = [];

        for ($i = 0; $i < $quantity; $i++) {
            $ids[] = $this->generate();
        }

        return $ids;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function isValid(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $timestamp = $id >> self::TIMESTAMP_SHIFT;

        return $timestamp > 0
            && $timestamp <= self::MAX_TIMESTAMP;
    }
}