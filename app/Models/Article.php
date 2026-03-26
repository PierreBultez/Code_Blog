<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'meta_description',
        'og_image',
        'og_text',
        'content',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (! $article->slug) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function seoDescription(): Attribute
    {
        return Attribute::get(fn (): string => $this->meta_description ?? Str::limit($this->excerpt ?? '', 160, ''));
    }

    /**
     * @return Attribute<string, never>
     */
    protected function ogImageUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->og_image) {
                return asset("storage/{$this->og_image}");
            }

            return route('articles.og-image', $this);
        });
    }

    /**
     * @return Attribute<int, never>
     */
    protected function readingTime(): Attribute
    {
        return Attribute::get(fn (): int => max(1, (int) ceil(str_word_count(strip_tags($this->content ?? '')) / 200)));
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
