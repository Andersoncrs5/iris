<?php

namespace App\Adapter\Out\Persistence\Repository;

use App\Port\Out\GenericRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @template T of object
 * @template M of Model
 * @template ID of int|string
 * @implements GenericRepositoryInterface<T, ID>
 */
abstract class GenericEloquentRepository implements GenericRepositoryInterface
{
    /**
     * @param class-string<M> $modelClass Nome da classe do Eloquent Model
     */
    public function __construct(
        protected string $modelClass
    ) {}

    /**
     * @param M $model
     * @return T
     */
    abstract protected function toDomain(Model $model): object;

    /**
     * @param T $entity
     * @return array<string, mixed>
     */
    abstract protected function toDatabase(object $entity): array;

    public function findById(mixed $id): ?object
    {
        /** @var M|null $model */
        $model = $this->modelClass::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByIdForUpdate(mixed $id): ?object
    {
        /** @var M|null $model */
        $model = $this->modelClass::where('id', $id)->lockForUpdate()->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function existsById(mixed $id): bool
    {
        return $this->modelClass::where('id', $id)->exists();
    }

    public function deleteById(mixed $id): bool
    {
        return (bool) $this->modelClass::destroy($id);
    }

    public function save(object $entity): object
    {
        $data = $this->toDatabase($entity);
        
        /** @var M $model */
        $model = $this->modelClass::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        return $this->toDomain($model);
    }

    public function saveAll(array $entities): array
    {
        $savedEntities = [];
        foreach ($entities as $entity) {
            $savedEntities[] = $this->save($entity);
        }

        return $savedEntities;
    }

    public function countAll(): int
    {
        return $this->modelClass::count();
    }

    public function deleteAll(): int
    {
        return $this->modelClass::query()->delete();
    }
}