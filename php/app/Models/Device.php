<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Device extends Model
{
    use HasFactory, HasUuids;
    public $incrementing = false;
    protected $fillable = [
        'user_id',
        'hostname',
        'disk_total',
        'disk_free',
        'disk_used'
    ];
}
