<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableMapAnalisys extends Model
{
    use HasFactory;

    protected $table = 'variables_map_analiyis';

    protected $fillable = [
        'id',
        'description',
        'score',
        'zone_id',
        'user_id',
        'state',
        'tried_id',
        'edits',
    ];

    // Deshabilitar el auto-increment para permitir asignación manual de IDs
    public $incrementing = false;
}
