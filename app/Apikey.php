<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apikey extends Model
{
    protected $table = 'cb_apikeys';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'apikey', 'account_id'
    ];
}
