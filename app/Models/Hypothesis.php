<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hypothesis extends Model
{
    protected $table = 'hypothesis';
    public $timestamps = true;
    protected $fillable = [
        'id',
        'id_variable',
        'zone_id',
        'descriptionH0',
        'descriptionH1',
        'user_id',
        'state',
    ];
}
