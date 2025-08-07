<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conclusion extends Model
{
    use HasFactory;

    protected $table = 'conclusions';

    protected $fillable = [
        'component_practice',
        'component_practice_edits',
        'actuality',
        'actuality_edits',
        'aplication',
        'aplication_edits',
        'state',
        'user_id',
        'tried_id'
    ];

    protected $casts = [
        'state' => 'string',
        'user_id' => 'integer',
        'component_practice_edits' => 'integer',
        'actuality_edits' => 'integer',
        'aplication_edits' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getByUser($userId)
    {
        return self::where('user_id', $userId)->first();
    }

    public static function createOrUpdate($data, $userId)
    {
        return self::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    public function isAllBlocked()
    {
        return $this->state === '1';
    }

    public function block()
    {
        $this->state = '1';
        return $this->save();
    }

    public function unblock()
    {
        $this->state = '0';
        return $this->save();
    }

    public function areAllFieldsBlocked()
    {
        return $this->component_practice_edits >= 3 &&
               $this->actuality_edits >= 3 &&
               $this->aplication_edits >= 3;
    }
}