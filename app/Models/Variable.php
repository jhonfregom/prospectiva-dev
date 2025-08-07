<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    use HasFactory;  

    protected $fillable = [
        'id',
        'id_variable',
        'name_variable',
        'description',
        'score',
        'state',
        'user_id',
        'now_condition',
        'tried_id',
    ];

    protected $casts = [
        'score' => 'integer',
        'user_id' => 'integer',
        'state' => 'string'
    ];

    protected $attributes = [
        'description' => '',
        'score' => 0,
        'state' => '0'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}