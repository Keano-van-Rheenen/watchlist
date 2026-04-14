<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Watchable extends Model
{
    use HasUuids;

    protected $table = 'watchable';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'title',
        'summary',
        'duration',
        'picture',
    ];
}
