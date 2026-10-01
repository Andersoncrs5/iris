<?php

namespace App\Utils\Snowflake;

interface IdGeneratorInterface
{
    public function generate(): int;
}