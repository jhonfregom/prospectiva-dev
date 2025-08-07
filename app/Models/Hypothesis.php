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
        'name_hypothesis',
        'description',
        'secondary_hypotheses',
        'user_id',
        'state',
        'tried_id',
        'edits',
    ];
}