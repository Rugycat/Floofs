<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = [
    'user_id', 'name', 'species', 'breed', 'age', 'photo_path',
];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function healthRecords()
{
    return $this->hasMany(HealthRecord::class);
}

}

