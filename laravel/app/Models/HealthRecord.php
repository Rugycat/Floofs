<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    public function pet()
{
    return $this->belongsTo(Pet::class);
}

}
