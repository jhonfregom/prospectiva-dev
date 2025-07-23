<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scenarios extends Model
{
    protected $table = 'scenarios';
    public $timestamps = true;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'user_id',
        'titulo',
        'edits',
        'state',
        'num_scenario',
        'year1',
        'year2',
        'year3',
        'edits_year1',
        'edits_year2',
        'edits_year3',
        'tried_id',
    ];
} 