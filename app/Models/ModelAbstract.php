<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Collections\Collection;

abstract class ModelAbstract extends Model
{
    protected $primaryKey;

    public function __construct(array $attributes = [])
    {
        if (!isset($this->primaryKey)) {
            $this->primaryKey = $this->getKeyName();
        }

        parent::__construct($attributes);
    }

    public function isEmpty()
    {
        return !count($this->attributes);
    }

    public function getCasts()
    {
        return $this->casts;
    }

    public function hasCast($key, $types = null)
    {
        return false;
    }

    /**
     * Override Model::getTable so we can avoid nasty/inconsistent pluralization
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        return strtolower(snake_case(class_basename($this)));
    }

    /**
     * Override Model::getKey to avoid ambiguity in our primary key naming conventions
     * @return string
     */
    public function getKeyName()
    {
        if (isset($this->primaryKey)) {
            return $this->primaryKey;
        }

        return $this->getTable() . '_' . 'id';
    }

    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    public function hasGetMutator($key)
    {
        return false;
    }

    public function getDates()
    {
        return [];
    }

    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        return $value;
    }

    public function isSameAs(Model $model)
    {
        return (
            get_class($this) === get_class($model) &&
            $this->getKey() === $model->getKey()
        );
    }
}