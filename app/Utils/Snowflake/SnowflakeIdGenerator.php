<?php

namespace App\Utils\Snowflake;

use Godruoyi\Snowflake\Snowflake;

class SnowflakeIdGenerator implements IdGeneratorInterface
{
    private Snowflake $snowflake;

    public function __construct(int $workerId = 1)
    {
        $this->snowflake = new Snowflake($workerId);
    }

    public function generate(): int
    {
        return (int) $this->snowflake->id();
    }
}