<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = Str::uuid()->toString();
            }
        });
    }

    public function getUuidColumn()
    {
        return 'uuid';
    }

    public function getRouteKeyName()
    {
        return $this->getUuidColumn();
    }

    public function scopeWhereUuid($query, $uuid)
    {
        return $query->where($this->getUuidColumn(), $uuid);
    }

    public static function findByUuid($uuid)
    {
        return static::whereUuid($uuid)->first();
    }
}