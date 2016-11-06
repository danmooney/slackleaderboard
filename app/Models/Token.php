<?php

namespace App\Models;

class Token extends ModelAbstract
{
    protected $primaryKey = 'user_id';

    public function getTokenAttribute($value)
    {
        return decrypt($value);
    }

    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = encrypt($value);
    }
}