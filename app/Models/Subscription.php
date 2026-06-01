<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;



    protected $fillable = [
        'client_id',
        'application_id',
        'type_app',
        'license_key',
        'max_devices',
        'duration',
        'start_date',
        'expiry_date',
        'is_active',
        'note',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'application_id' => 'integer',
        'type_app' => 'integer',
        'is_active' => 'integer',
        'duration' => 'integer',
        'max_devices' => 'integer',
        'licenses_count' => 'integer',
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }
}
