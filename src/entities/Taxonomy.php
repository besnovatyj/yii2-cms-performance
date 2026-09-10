<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\entities;

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\queries\TaxonomyQuery;
use Besnovatyj\Meta\MetaBehavior;
use Besnovatyj\Meta\Meta;
use Besnovatyj\TreeManager\Manager\entities\Node;
use yii\db\ActiveQuery;

/**
 * @property integer $id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property integer $tree
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $status
 * @property int $sort_order - Порядок сортировки корневых узлов
 *
 * @property Meta $meta
 *
 * @mixin MetaBehavior
 */
class Taxonomy extends Node
{
    /** Раздел снят с публикации: не показывается на фронте и не участвует в поиске. */
    public const int STATUS_INACTIVE = 0;

    /** Раздел опубликован. */
    public const int STATUS_ACTIVE = 1;

    public Meta $meta;

    public static function create($name, $slug, $description, Meta $meta): self
    {
        $taxonomy = new static();
        $taxonomy->name = $name;
        $taxonomy->slug = $slug;
        $taxonomy->description = $description;
        $taxonomy->meta = $meta;
        return $taxonomy;
    }

    public function edit($name, $slug, $description, Meta $meta): void
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->meta = $meta;
    }

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->name;
    }

    public function changeStatus(): void
    {
        $this->status = !$this->status;
    }

    /**
     * Опубликован ли раздел сам по себе (без учёта предков — см. {@see TaxonomyQuery::visible()}).
     */
    public function isActive(): bool
    {
        return (int)$this->status === self::STATUS_ACTIVE;
    }

    public function getPerformances(): ActiveQuery
    {
        return $this->hasMany(Performance::class, ['taxonomy_id' => 'id']);
    }

    public static function tableName(): string
    {
        return '{{%performance_taxonomies}}';
    }

    public function countPerformancesByTaxonomy(): bool|int|string|null
    {
        return $this->getPerformances()->count();
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
            ...parent::behaviors()
        ];
    }

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find(): TaxonomyQuery
    {
        return new TaxonomyQuery(static::class);
    }
}
