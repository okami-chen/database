<?php

namespace OkamiChen\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CustomSoftDeletingScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['Restore', 'RestoreOrCreate', 'CreateOrRestore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (strstr($model->getQualifiedDeletedAtColumn(), 'time')) {
            $builder->where($model->getQualifiedDeletedAtColumn(), 0);
            return;
        }
        $builder->whereNull($model->getQualifiedDeletedAtColumn());
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getDeletedAtColumn($builder);

            if (strstr($column, 'time')) {
                return $builder->update([
                    $column => $builder->getModel()->freshTimestamp()->timestamp,
                ]);
            }
            return $builder->update([
                $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Get the "deleted at" column for the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return string
     */
    protected function getDeletedAtColumn(Builder $builder)
    {
        if (count((array)$builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDeletedAtColumn();
        }

        return $builder->getModel()->getDeletedAtColumn();
    }

    /**
     * Add the restore extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addRestore(Builder $builder)
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();
            // addon
            if (strstr($builder->getModel()->getDeletedAtColumn(), 'time')) {
                return $builder->update([$builder->getModel()->getDeletedAtColumn() => 0]);
            }
            return $builder->update([$builder->getModel()->getDeletedAtColumn() => null]);
        });
    }

    /**
     * Add the restore-or-create extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addRestoreOrCreate(Builder $builder)
    {
        $builder->macro('restoreOrCreate', function (Builder $builder, array $attributes = [], array $values = []) {
            $builder->withTrashed();

            return tap($builder->firstOrCreate($attributes, $values), function ($instance) {
                $instance->restore();
            });
        });
    }

    /**
     * Add the create-or-restore extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addCreateOrRestore(Builder $builder)
    {
        $builder->macro('createOrRestore', function (Builder $builder, array $attributes = [], array $values = []) {
            $builder->withTrashed();

            return tap($builder->createOrFirst($attributes, $values), function ($instance) {
                $instance->restore();
            });
        });
    }

    /**
     * Add the with-trashed extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithTrashed(Builder $builder)
    {
        $builder->macro('withTrashed', function (Builder $builder, $withTrashed = true) {
            if (!$withTrashed) {
                return $builder->withoutTrashed();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithoutTrashed(Builder $builder)
    {
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            if (strstr($model->getQualifiedDeletedAtColumn(), 'time')) {
                $builder->withoutGlobalScope($this)->where(
                    $model->getQualifiedDeletedAtColumn(), '=', 0
                );
                return $builder;
            }
            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedDeletedAtColumn()
            );

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addOnlyTrashed(Builder $builder)
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            if (strstr($model->getQualifiedDeletedAtColumn(), 'time')) {
                $builder->withoutGlobalScope($this)->where(
                    $model->getQualifiedDeletedAtColumn(), '>', 0
                );
            } else {
                $builder->withoutGlobalScope($this)->whereNotNull(
                    $model->getQualifiedDeletedAtColumn()
                );
            }


            return $builder;
        });
    }
}
