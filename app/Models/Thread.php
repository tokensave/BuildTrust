<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = ['ad_id'];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'thread_user')
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    #[Scope]
    public function scopeHasUsers(Builder $query, int $ad_id, int $author_id, int $recipient_id): void
    {
        $query->where('ad_id', $ad_id)
            ->whereHas('participants', function ($q) use ($author_id) {
                $q->where('user_id', $author_id);
            })
            ->whereHas('participants', function ($q) use ($recipient_id) {
                $q->where('user_id', $recipient_id);
            })
            ->withCount('participants')
            ->having('participants_count', '=', 2);
    }

    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
