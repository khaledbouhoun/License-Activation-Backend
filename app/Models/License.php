<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    

    protected $fillable = [
        'subscription_id',
        'device_id',
        'device_model',
        'last_sync_date',
        'start_date',
        'expiry_date',
    ];

    protected $casts = [
        'last_sync_date' => 'datetime',
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
