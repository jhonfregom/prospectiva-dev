<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matriz extends Model
{
    use HasFactory;

    protected $table = 'matriz';
    
    protected $fillable = [
        'id',
        'id_matriz',
        'id_variable',
        'id_resp_depen',
        'id_resp_influ',
        'user_id',
        'tried_id',
        'state'
    ];

    protected $casts = [
        'id_matriz' => 'integer',
        'id_variable' => 'integer',
        'id_resp_depen' => 'integer',
        'id_resp_influ' => 'integer',
        'user_id' => 'integer',
        'tried_id' => 'integer',
        'state' => 'string'
    ];

    protected $attributes = [
        'state' => '0'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variable()
    {
        return $this->belongsTo(Variable::class, 'id_variable');
    }

    public function traceability()
    {
        return $this->belongsTo(Traceability::class, 'tried_id');
    }
}