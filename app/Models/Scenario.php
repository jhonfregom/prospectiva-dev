<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
    use HasFactory;

    protected $table = 'scenarios';

    protected $fillable = [
        'titulo',
        'texto',
        'year1',
        'year2',
        'year3',
        'user_id',
    ];
} 