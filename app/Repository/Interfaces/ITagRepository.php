<?php

namespace App\Repository\Interfaces;

use App\Models\TagModel;

interface ITagRepository
{
    public function findById(int $id): ?TagModel;
    public function findByIdForUpdate(int $id): ?TagModel;
    
    public function count(): int;

    public function existsById(int $id): bool;
    public function existsByName(string $name): bool;
    public function existsBySlug(string $slug): bool;

    public function save(TagModel $tag): TagModel;
    public function update(TagModel $tag): TagModel;

    public function deleteById(int $id): bool;
    public function deleteByIdForce(int $id): bool;
    public function restoreById(int $id): bool;
}