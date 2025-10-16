<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    protected $fillable = [
    'pet_id',
    'weight',
    'vaccine',
    'illness_history',
    'recommendations',
];

    public function pet()
{
    return $this->belongsTo(Pet::class);
}

}
