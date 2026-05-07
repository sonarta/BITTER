<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'instructor_id', 'title', 'slug', 'tagline', 'description', 'category',
    'level', 'cover_url', 'price', 'status', 'published_at',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * @return HasMany<Module, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    /**
     * @return HasManyThrough<Lesson, Module, $this>
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasOne<CourseExam, $this>
     */
    public function exam(): HasOne
    {
        return $this->hasOne(CourseExam::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function coverMeta(): array
    {
        $source = $this->coverSource();

        return [
            'url' => $source === 'manual' ? (string) $this->cover_url : self::placeholderCoverDataUri(),
            'source' => $source,
        ];
    }

    public function coverSource(): string
    {
        if (blank($this->cover_url)) {
            return 'placeholder';
        }

        if ($this->isLegacyDefaultCoverUrl($this->cover_url)) {
            return 'placeholder';
        }

        return 'manual';
    }

    private function isLegacyDefaultCoverUrl(string $url): bool
    {
        return str_contains($url, 'images.unsplash.com/photo-1555066931-4365d14bab8c');
    }

    private static function placeholderCoverDataUri(): string
    {
        return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPSc4MDAnIGhlaWdodD0nNDUwJyB2aWV3Qm94PScwIDAgODAwIDQ1MCc+PGRlZnM+PGxpbmVhckdyYWRpZW50IGlkPSdnJyB4MT0nMCcgeTE9JzAnIHgyPScxJyB5Mj0nMSc+PHN0b3Agb2Zmc2V0PScwJyBzdG9wLWNvbG9yPScjMGYxNzJhJy8+PHN0b3Agb2Zmc2V0PScxJyBzdG9wLWNvbG9yPScjMWQ0ZWQ4Jy8+PC9saW5lYXJHcmFkaWVudD48L2RlZnM+PHJlY3Qgd2lkdGg9JzgwMCcgaGVpZ2h0PSc0NTAnIGZpbGw9J3VybCgjZyknLz48dGV4dCB4PSc1MCUnIHk9JzUwJScgZG9taW5hbnQtYmFzZWxpbmU9J21pZGRsZScgdGV4dC1hbmNob3I9J21pZGRsZScgZm9udC1mYW1pbHk9J3N5c3RlbS11aSwgLWFwcGxlLXN5c3RlbSwgU2Vnb2UgVUksIFJvYm90bywgQXJpYWwnIGZvbnQtc2l6ZT0nNDgnIGZpbGw9JyNmZmZmZmYnIG9wYWNpdHk9JzAuOTInPk5vIENvdmVyPC90ZXh0Pjwvc3ZnPg==';
    }
}
