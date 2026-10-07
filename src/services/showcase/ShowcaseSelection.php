<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\showcase;

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\performance\queries\PerformanceQuery;
use Besnovatyj\Performance\entities\showcase\Order;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\ShowcaseItem;
use Besnovatyj\Performance\entities\showcase\Source;
use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\db\Expression;

/**
 * Запрос спектаклей витрины — один на фронт и на предпросмотр в админке, чтобы админка
 * показывала ровно тот отбор, который увидит посетитель.
 *
 * Отбор (раздел, период премьеры) действует только у витрины по правилу. Публичность
 * ({@see PerformanceQuery::visible()}) — всегда: скрытый спектакль или раздел не должен
 * попасть на сайт и через витрину.
 *
 * Элементы витрины присоединяются как `si`: у ручной витрины это её состав, у витрины по
 * правилу — поправки (скрыть, ручной порядок).
 */
final readonly class ShowcaseSelection
{
    /** Алиас таблицы спектаклей в запросе. */
    public const string ALIAS = 'p';

    /** Алиас таблицы элементов витрины в запросе. */
    public const string ITEM_ALIAS = 'si';

    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    /**
     * @param Showcase $showcase
     * @param DateRange $range период премьеры на сегодня (для ручной витрины не используется)
     * @param bool $withHidden включить скрытые элементы (для админки); на фронте — false
     * @return PerformanceQuery
     */
    public function query(Showcase $showcase, DateRange $range, bool $withHidden = false): PerformanceQuery
    {
        $p = self::ALIAS;
        $si = self::ITEM_ALIAS;
        $rule = $showcase->rule();

        $query = Performance::find()->alias($p)->visible($p)
            ->leftJoin(
                [$si => ShowcaseItem::tableName()],
                "{$si}.performance_id = {$p}.id AND {$si}.showcase_id = :showcaseId",
                [':showcaseId' => (int)$showcase->id],
            );

        if ($rule->source === Source::Manual) {
            $query->andWhere(['not', ["{$si}.id" => null]]);
        } else {
            $this->applyTaxonomy($query, $rule->taxonomyId, $rule->withDescendants);
            $this->applyRange($query, $range);
        }

        if (!$withHidden) {
            $query->andWhere(['or', ["{$si}.id" => null], ["{$si}.status" => ShowcaseItem::STATUS_ACTIVE]]);
        }

        return $query->orderBy($this->orderOf($rule->order));
    }

    /**
     * @param PerformanceQuery $query
     * @param int|null $taxonomyId
     * @param bool $withDescendants
     * @return void
     */
    private function applyTaxonomy(PerformanceQuery $query, ?int $taxonomyId, bool $withDescendants): void
    {
        if ($taxonomyId === null) {
            return;
        }

        $taxonomy = Taxonomy::findOne($taxonomyId);
        if ($taxonomy === null) {
            // Раздел пропал — витрина пуста, а не «вся афиша».
            $query->andWhere('0 = 1');
            return;
        }

        $ids = $withDescendants ? $this->treeScope->descendantIds($taxonomy, andSelf: true) : [$taxonomy->id];
        $query->andWhere([self::ALIAS . '.taxonomy_id' => $ids]);
    }

    /**
     * @param PerformanceQuery $query
     * @param DateRange $range
     * @return void
     */
    private function applyRange(PerformanceQuery $query, DateRange $range): void
    {
        $column = self::ALIAS . '.premiere_date';

        if ($range->from !== null) {
            $query->andWhere(['>=', $column, $range->from->format('Y-m-d')]);
        }
        if ($range->to !== null) {
            $query->andWhere(['<=', $column, $range->to->format('Y-m-d')]);
        }
    }

    /**
     * @param Order $order
     * @return array
     */
    private function orderOf(Order $order): array
    {
        $p = self::ALIAS;
        $si = self::ITEM_ALIAS;

        return match ($order) {
            Order::PremiereDesc => ["{$p}.premiere_date" => SORT_DESC, "{$p}.id" => SORT_DESC],
            Order::PremiereAsc => ["{$p}.premiere_date" => SORT_ASC, "{$p}.id" => SORT_ASC],
            Order::Title => ["{$p}.title" => SORT_ASC, "{$p}.id" => SORT_ASC],
            Order::Manual => [
                // Сначала спектакли с заданным местом, по месту; остальные — следом, от свежей премьеры.
                'manual_last' => new Expression("{$si}.sort IS NULL"),
                "{$si}.sort" => SORT_ASC,
                "{$p}.premiere_date" => SORT_DESC,
                "{$p}.id" => SORT_DESC,
            ],
        };
    }
}
