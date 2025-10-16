<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $fillable = [
        'health_record_id','title','description','scheduled_at','status'
    ];

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class);
    }
}
