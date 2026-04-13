<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Presentation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'join_code',
        'manage_token',
        'is_live',
    ];

    protected function casts(): array
    {
        return [
            'is_live' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function responses(): HasManyThrough
    {
        return $this->hasManyThrough(Response::class, Question::class);
    }
}
