<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @method static Builder<static>|Post newModelQuery()
 * @method static Builder<static>|Post newQuery()
 * @method static Builder<static>|Post news()
 * @method static Builder<static>|Post query()
 * @method static Builder<static>|Post video()
 * @method static Builder<static>|Post whereCreatedAt($value)
 * @method static Builder<static>|Post whereDescription($value)
 * @method static Builder<static>|Post whereId($value)
 * @method static Builder<static>|Post whereTitle($value)
 * @method static Builder<static>|Post whereType($value)
 * @method static Builder<static>|Post whereUpdatedAt($value)
 * @mixin Builder
 */
class Post extends Model
{
    use HasFactory;

    public const string TYPE_VIDEO = 'video';
    public const string TYPE_NEWS = 'news';

    protected $fillable = [
        'type',
        'title',
        'description',
    ];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->latest();
    }

    public function scopeVideo($query)
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    public function scopeNews($query)
    {
        return $query->where('type', self::TYPE_NEWS);
    }
}

