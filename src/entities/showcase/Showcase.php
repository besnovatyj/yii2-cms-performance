<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

use Besnovatyj\Performance\entities\Taxonomy;
use DateTimeImmutable;
use DomainException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Витрина спектаклей: именованная выборка, которую тема или шорткод выводят по коду.
 *
 * Правило хранится плоскими колонками, а наружу отдаётся одним объектом {@see Rule} — так
 * им пользуются расчёт периода и выборка, не зная о схеме таблицы.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $status
 * @property int $sort
 * @property string $source
 * @property int|null $taxonomy_id
 * @property int|bool $with_descendants
 * @property string $from_mode
 * @property string|null $from_date
 * @property int $from_offset_months
 * @property string $from_snap
 * @property string $to_mode
 * @property string $order_by
 * @property int|null $items_limit
 *
 * @property ShowcaseItem[] $items
 * @property Taxonomy|null $taxonomy
 */
class Showcase extends ActiveRecord
{
    public const int STATUS_DRAFT = 0;
    public const int STATUS_ACTIVE = 1;

    /**
     * @param string $code
     * @param string $name
     * @param int $sort
     * @param Rule $rule
     * @return self
     */
    public static function create(string $code, string $name, int $sort, Rule $rule): self
    {
        $showcase = new static();
        $showcase->status = self::STATUS_DRAFT;
        $showcase->edit($code, $name, $sort, $rule);
        return $showcase;
    }

    /**
     * @param string $code
     * @param string $name
     * @param int $sort
     * @param Rule $rule
     * @return void
     */
    public function edit(string $code, string $name, int $sort, Rule $rule): void
    {
        if (trim($name) === '') {
            throw new DomainException('Showcase name cannot be empty.');
        }
        if (trim($code) === '') {
            throw new DomainException('Showcase code cannot be empty.');
        }

        $this->code = $code;
        $this->name = $name;
        $this->sort = $sort;
        $this->applyRule($rule);
    }

    /**
     * Правило витрины одним объектом.
     *
     * @return Rule
     */
    public function rule(): Rule
    {
        return new Rule(
            source: Source::tryFrom((string)$this->source) ?? Source::Rule,
            taxonomyId: $this->taxonomy_id !== null ? (int)$this->taxonomy_id : null,
            withDescendants: (bool)$this->with_descendants,
            fromMode: FromMode::tryFrom((string)$this->from_mode) ?? FromMode::None,
            fromDate: $this->from_date ? new DateTimeImmutable((string)$this->from_date) : null,
            fromOffsetMonths: (int)$this->from_offset_months,
            fromSnap: Snap::tryFrom((string)$this->from_snap) ?? Snap::None,
            toMode: ToMode::tryFrom((string)$this->to_mode) ?? ToMode::Today,
            order: Order::tryFrom((string)$this->order_by) ?? Order::PremiereDesc,
            limit: $this->items_limit !== null ? (int)$this->items_limit : null,
        );
    }

    /**
     * @return bool
     */
    public function isManual(): bool
    {
        return $this->source === Source::Manual->value;
    }

    /**
     * @return void
     */
    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('Showcase is already active.');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return void
     */
    public function draft(): void
    {
        if ($this->isDraft()) {
            throw new DomainException('Showcase is already draft.');
        }
        $this->status = self::STATUS_DRAFT;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (int)$this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return (int)$this->status === self::STATUS_DRAFT;
    }

    /**
     * @param Rule $rule
     * @return void
     */
    private function applyRule(Rule $rule): void
    {
        $this->source = $rule->source->value;
        $this->taxonomy_id = $rule->taxonomyId;
        $this->with_descendants = $rule->withDescendants;
        $this->from_mode = $rule->fromMode->value;
        $this->from_date = $rule->fromDate?->format('Y-m-d');
        $this->from_offset_months = $rule->fromOffsetMonths;
        $this->from_snap = $rule->fromSnap->value;
        $this->to_mode = $rule->toMode->value;
        $this->order_by = $rule->order->value;
        $this->items_limit = $rule->limit;
    }

    // <editor-fold desc="Relations">

    /**
     * @return ActiveQuery
     */
    public function getItems(): ActiveQuery
    {
        return $this->hasMany(ShowcaseItem::class, ['showcase_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTaxonomy(): ActiveQuery
    {
        return $this->hasOne(Taxonomy::class, ['id' => 'taxonomy_id']);
    }

    // </editor-fold>

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%performance_showcases}}';
    }
}
