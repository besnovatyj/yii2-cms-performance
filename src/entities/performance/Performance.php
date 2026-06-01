<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\entities\performance;

use Besnovatyj\Helpers\FilesystemHelper;
use Besnovatyj\Meta\MetaBehavior;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\entities\performance\queries\PerformanceQuery;
use Besnovatyj\Performance\entities\Tag;
use Besnovatyj\PessimisticLock\PessimisticLockBehavior;
use DateTimeImmutable;
use DomainException;
use Besnovatyj\DomainEvents\AggregateRoot;
use Besnovatyj\Meta\Meta;
use Besnovatyj\DomainEvents\EventTrait;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $title
 * @property string $description
 * @property integer $taxonomy_id
 * @property integer $main_image_id
 * @property string $author
 * @property string $genre
 * @property string $production_group
 * @property string $actors
 * @property string $age_limit
 * @property integer $status
 *
 * @property Meta $meta
 * @property Taxonomy $taxonomy
 * @property TagAssignment[] $tagAssignments
 * @property Tag[] $tags
 * @property Image[] $images
 * @property Image $mainImage
 *
 * @mixin PessimisticLockBehavior
 */
class Performance extends ActiveRecord implements AggregateRoot
{
    use EventTrait;

    public const int STATUS_DRAFT = 0;
    public const int STATUS_ACTIVE = 1;

    public Meta $meta;

    public static function create($taxonomyId, $title, $author, $genre, $description, $production_group, $actors, $age_limit, $status, Meta $meta): self
    {
        $performance = new static();
        $performance->taxonomy_id = $taxonomyId;
        $performance->title = $title;
        $performance->author = $author;
        $performance->genre = $genre;
        $performance->description = $description;
        $performance->production_group = $production_group;
        $performance->actors = $actors;
        $performance->age_limit = $age_limit;
        $performance->status = $status;
        $performance->created_at = new DateTimeImmutable()->format('Y.m.d H:i:s');
        $performance->meta = $meta;
        return $performance;
    }

    public function edit($title, $author, $genre, $description, $production_group, $actors, $age_limit, $status, Meta $meta): void
    {
        $this->title = $title;
        $this->author = $author;
        $this->genre = $genre;
        $this->description = $description;
        $this->production_group = $production_group;
        $this->actors = $actors;
        $this->age_limit = $age_limit;
        $this->status = $status;
        $this->meta = $meta;
        $this->updated_at = new DateTimeImmutable()->format('Y.m.d H:i:s');
    }

    public function changeMainTaxonomy($taxonomyId): void
    {
        $this->taxonomy_id = $taxonomyId;
    }

    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('Already enabled.');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    public function draft(): void
    {
        if ($this->isDraft()) {
            throw new DomainException('Already disabled.');
        }
        $this->status = self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->title;
    }

    // <editor-fold desc="Tags">
    // Tag assignment methods moved to PerformanceManageService
    // </editor-fold>

    // <editor-fold desc="Images">
    // Image manipulation methods moved to PerformanceManageService

    public function setMainImage(?int $imageId): void
    {
        $this->main_image_id = $imageId;
    }

    // </editor-fold>

    // <editor-fold desc="Relations">

    public function getTaxonomy(): ActiveQuery
    {
        return $this->hasOne(Taxonomy::class, ['id' => 'taxonomy_id']);
    }

    public function getTagAssignments(): ActiveQuery
    {
        return $this->hasMany(TagAssignment::class, ['performance_id' => 'id']);
    }

    public function getTags(): ActiveQuery
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('tagAssignments');
    }

    public function getImages(): ActiveQuery
    {
        return $this->hasMany(Image::class, ['performance_id' => 'id'])->orderBy('sort');
    }

    public function getMainImage(): ActiveQuery
    {
        return $this->hasOne(Image::class, ['id' => 'main_image_id']);
    }

    // </editor-fold>

    // <editor-fold desc="Events">

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            if ($this->images) {
                foreach ($this->images as $image) {
                    $image->delete();
                }
            }

            $origin = Yii::getAlias('@static/origin/Performance') . '/' . $this->id;
            $cache = Yii::getAlias('@static/cache/Performance') . '/' . $this->id;
            FilesystemHelper::deleteDirContents($origin, true);
            FilesystemHelper::deleteDirContents($cache, true);

            return true;
        }
        return false;
    }

    // </editor-fold>

    public static function tableName(): string
    {
        return '{{%performance_performances}}';
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
            PessimisticLockBehavior::class,
            ...parent::behaviors(),
        ];
    }

    public static function find(): PerformanceQuery
    {
        return new PerformanceQuery(static::class);
    }
}
