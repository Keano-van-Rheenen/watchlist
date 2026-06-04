<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityWatchable extends Model
{
    use HasUuids;
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'uploader_user_id',
        'hierarchy_index',
        'kind',
        'title',
        'summary',
        'duration',
        'episodes',
        'picture',
    ];

    protected $casts = [
        'hierarchy_index' => 'integer',
        'episodes' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommunityWatchableVote::class);
    }
}