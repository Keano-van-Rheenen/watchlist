<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityWatchableVote extends Model
{
    use HasUuids;
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'community_watchable_id',
        'user_id',
    ];

    public function communityWatchable(): BelongsTo
    {
        return $this->belongsTo(CommunityWatchable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}