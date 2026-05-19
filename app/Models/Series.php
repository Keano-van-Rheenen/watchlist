<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'hierarchy_index',
        'title',
        'summary',
        'episodes',
        'picture',
    ];

    protected $casts = [
        'hierarchy_index' => 'integer',
        'episodes' => 'integer',
    ];

    use HasFactory;

}
