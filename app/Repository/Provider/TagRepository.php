<?php

namespace App\Repository;

use App\Models\TagModel;
use App\Repository\Interfaces\ITagRepository;
use App\Utils\Snowflake\IdGeneratorInterface;

class TagRepository implements ITagRepository
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function findById(int $id): ?TagModel
    {
        return TagModel::find($id);
    }

    public function findByIdForUpdate(int $id): ?TagModel
    {
        return TagModel::lockForUpdate()->find($id);
    }

    public function count(): int
    {
        return TagModel::count();
    }

    public function existsById(int $id): bool
    {
        return TagModel::where('id', $id)->exists();
    }

    public function existsByName(string $name): bool
    {
        return TagModel::where('name', $name)->exists();
    }

    public function existsBySlug(string $slug): bool
    {
        return TagModel::where('slug', $slug)->exists();
    }

    public function save(TagModel $tag): TagModel
    {
        if (empty($tag->id)) {
            $tag->id = $this->idGenerator->generate();
        }

        $tag->save();

        return $tag;
    }

    public function update(TagModel $tag): TagModel
    {
        $tag->save();

        return $tag;
    }

    public function deleteById(int $id): bool
    {
        return TagModel::where('id', $id)->delete() > 0;
    }

    public function deleteByIdForce(int $id): bool
    {
        return TagModel::withTrashed()->where('id', $id)->forceDelete() > 0;
    }

    public function restoreById(int $id): bool
    {
        return TagModel::onlyTrashed()->where('id', $id)->restore() > 0;
    }
}