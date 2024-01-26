<?php

namespace Digisource\Core\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Digisource\Core\Exceptions\RepositoryException;
use Digisource\Core\Exceptions\EntityNotFoundException;
use Illuminate\Support\Facades\Validator;

abstract class EloquentRepository extends BaseRepository
{
    /**
     * @var array
     * rules to validate for model attributes
     */
    protected $rules = [];
    /**
     * @var array
     * validation error messages
     */
    protected $errors;

    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        // make a new validator object
        $v = Validator::make($data, $this->rules);

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->messages();
            return false;
        }

        // validation pass
        return true;
    }

    public function errors()
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail($id, $attributes = ['*'])
    {
        $result = $this->find($id, $attributes);

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (!is_null($result)) {
            return $result;
        }

        throw new EntityNotFoundException($this->getModel(), $id);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $attributes = ['*'])
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($id, $attributes) {
            $query = $this->prepareQuery($this->createModel());
            return $query->find($id, $attributes);
        });
    }

    public function createModel()
    {
        if (is_string($model = $this->getModel())) {
            if (!class_exists($class = '\\' . ltrim($model, '\\'))) {
                throw new RepositoryException("Class {$model} does NOT exist!");
            }
            $model = $this->getContainer()->make($class);
        }

        // Set the connection used by the model
        if (!empty($this->connection)) {
            $model = $model->setConnection($this->connection);
        }

        if (!$model instanceof Model) {
            throw new RepositoryException(
                "Class {$model} must be an instance of \\Illuminate\\Database\\Eloquent\\Model"
            );
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findOrNew($id, $attributes = ['*'])
    {
        if (!is_null($entity = $this->find($id, $attributes))) {
            return $entity;
        }

        return $this->createModel();
    }


    /**
     * {@inheritdoc}
     */
    public function findBy($attribute, $value, $attributes = ['*'])
    {
        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            func_get_args(),
            function () use ($attribute, $value, $attributes) {
                $query = $this->prepareQuery($this->createModel());
                return $query->where($attribute, '=', $value)->first($attributes);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findFirst($attributes = ['*'])
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($attributes) {
            $query = $this->prepareQuery($this->createModel());
            return $query->first($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findAll($attributes = ['*'])
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($attributes) {
            $query = $this->prepareQuery($this->createModel());
            return $query->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function get($attributes = ['*'])
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($attributes) {
            $query = $this->prepareQuery($this->createModel());
            return $query->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $attributes = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            array_merge(func_get_args(), compact('page')),
            function () use ($perPage, $attributes, $pageName, $page) {
                $query = $this->prepareQuery($this->createModel());
                return $query->paginate($perPage, $attributes, $pageName, $page);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function simplePaginate($perPage = null, $attributes = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            array_merge(func_get_args(), compact('page')),
            function () use ($perPage, $attributes, $pageName, $page) {
                $query = $this->prepareQuery($this->createModel());
                return $query->simplePaginate($perPage, $attributes, $pageName, $page);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findWhere(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            func_get_args(),
            function () use ($where, $attributes) {
                [$attribute, $operator, $value, $boolean] = array_pad($where, 4, null);

                $this->where($attribute, $operator, $value, $boolean);
                $query = $this->prepareQuery($this->createModel());
                return $query->get($attributes);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereIn(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            func_get_args(),
            function () use ($where, $attributes) {
                [$attribute, $values, $boolean, $not] = array_pad($where, 4, null);

                $this->whereIn($attribute, $values, $boolean, $not);
                $query = $this->prepareQuery($this->createModel());
                return $query->get($attributes);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereNotIn(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            func_get_args(),
            function () use ($where, $attributes) {
                [$attribute, $values, $boolean] = array_pad($where, 3, null);

                $this->whereNotIn($attribute, $values, $boolean);
                $query = $this->prepareQuery($this->createModel());
                return $query->get($attributes);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereHas(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(
            static::class,
            __FUNCTION__,
            func_get_args(),
            function () use ($where, $attributes) {
                [$relation, $callback, $operator, $count] = array_pad($where, 4, null);

                $this->whereHas($relation, $callback, $operator, $count);
                $query = $this->prepareQuery($this->createModel());
                return $query->get($attributes);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $attributes = [], bool $syncRelations = false)
    {
        // Create a new instance
        $entity = $this->createModel();
        //get current logged-in user
        $user = auth()->user();
        // Fire the created event
        $this->getContainer('events')
            ->dispatch($this->getRepositoryId() . '.entity.creating', [$this, $entity, null, $user]);

        // Extract relationships
        if ($syncRelations) {
            $relations = $this->extractRelations($entity, $attributes);
            Arr::forget($attributes, array_keys($relations));
        }

        // Fill instance with data
        $entity->fill($attributes);

        // Save the instance
        $created = $entity->save();

        // Sync relationships
        if ($syncRelations && isset($relations)) {
            $this->syncRelations($entity, $relations);
        }

        // Fire the created event
        $this->getContainer('events')
            ->dispatch($this->getRepositoryId() . '.entity.created', [$this, $entity, null, $user]);

        // Return instance
        return $created ? $entity : $created;
    }

    /**
     * Extract relationships.
     *
     * @param mixed $entity
     * @param array $attributes
     *
     * @return array
     */
    protected function extractRelations($entity, array $attributes): array
    {
        $relations = [];
        $potential = array_diff(array_keys($attributes), $entity->getFillable());

        array_walk($potential, function ($relation) use ($entity, $attributes, &$relations) {
            if (method_exists($entity, $relation)) {
                $relations[$relation] = [
                    'values' => $attributes[$relation],
                    'class' => get_class($entity->{$relation}()),
                ];
            }
        });

        return $relations;
    }

    /**
     * Sync relationships.
     *
     * @param mixed $entity
     * @param array $relations
     * @param bool $detaching
     *
     * @return void
     */
    protected function syncRelations($entity, array $relations, $detaching = true): void
    {
        foreach ($relations as $method => $relation) {
            switch ($relation['class']) {
                case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                default:
                    $entity->{$method}()->sync((array)$relation['values'], $detaching);
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $attributes = [], bool $syncRelations = false)
    {
        $updated = false;
        //get current logged-in user
        $user = auth()->user();
        // Find the given instance
        $entity = $id instanceof Model ? $id : $this->find($id);

        if ($entity) {
            // Fire the updated event
            $this->getContainer('events')
                ->dispatch($this->getRepositoryId() . '.entity.updating', [$this, $entity, null, $user]);

            // Extract relationships
            if ($syncRelations) {
                $relations = $this->extractRelations($entity, $attributes);
                Arr::forget($attributes, array_keys($relations));
            }

            // Fill instance with data
            $entity->fill($attributes);

            //Check if we are updating attributes values
            $dirty = $entity->getDirty();
            $original = $entity->getOriginal();

            // Update the instance
            $updated = $entity->save();

            // Sync relationships
            if ($syncRelations && isset($relations)) {
                $this->syncRelations($entity, $relations);
            }

            if (count($dirty) > 0) {
                // Fire the updated event
                $this->getContainer('events')
                    ->dispatch($this->getRepositoryId() . '.entity.updated', [$this, $entity, $original, $user]);
            }
        }

        return $updated ? $entity : $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $deleted = false;

        // Find the given instance
        $entity = $id instanceof Model ? $id : $this->find($id);

        if ($entity) {
            //get current logged-in user
            $user = auth()->user();
            // Fire the deleted event
            $this->getContainer('events')
                ->dispatch($this->getRepositoryId() . '.entity.deleting', [$this, $entity, $user]);

            // Delete the instance
            $deleted = $entity->delete();

            // Fire the deleted event
            $this->getContainer('events')
                ->dispatch($this->getRepositoryId() . '.entity.deleted', [$this, $entity, $user]);
        }

        return $deleted ? $entity : $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function restore($id)
    {
        $restored = false;

        // Find the given instance
        $entity = $id instanceof Model ? $id : $this->find($id);

        if ($entity) {
            //get current logged-in user
            $user = auth()->user();
            // Fire the restoring event
            $this->getContainer('events')
                ->dispatch($this->getRepositoryId() . '.entity.restoring', [$this, $entity, $user]);

            // Restore the instance
            $restored = $entity->restore();

            // Fire the restored event
            $this->getContainer('events')
                ->dispatch($this->getRepositoryId() . '.entity.restored', [$this, $entity, $user]);
        }

        return $restored ? $entity : $restored;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        $this->getContainer('db')->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        $this->getContainer('db')->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack(): void
    {
        $this->getContainer('db')->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function count($columns = '*'): int
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($columns) {
            $query = $this->prepareQuery($this->createModel());
            return $query->count($columns);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function min($column)
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($column) {
            $query = $this->prepareQuery($this->createModel());
            return $query->min($column);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function max($column)
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($column) {
            $query = $this->prepareQuery($this->createModel());
            return $query->max($column);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function avg($column)
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($column) {
            $query = $this->prepareQuery($this->createModel());
            return $query->avg($column);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sum($column)
    {
        return $this->executeCallback(static::class, __FUNCTION__, func_get_args(), function () use ($column) {
            $query = $this->prepareQuery($this->createModel());
            return $query->sum($column);
        });
    }
}
