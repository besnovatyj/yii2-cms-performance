<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\repositories;

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\ShowcaseItem;
use Besnovatyj\Performance\services\showcase\DateRange;
use Besnovatyj\Performance\services\showcase\ShowcaseSelection;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * Репозиторий витрин спектаклей (админка).
 */
class ShowcaseRepository
{
    public function __construct(private readonly ShowcaseSelection $selection)
    {
    }

    /**
     * @param int $id
     * @return Showcase
     */
    public function get(int $id): Showcase
    {
        if (!$showcase = Showcase::findOne($id)) {
            throw new NotFoundException('Showcase is not found.');
        }
        return $showcase;
    }

    /**
     * Элемент витрины по спектаклю; null — элемента нет.
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @return ShowcaseItem|null
     */
    public function findItem(int $showcaseId, int $performanceId): ?ShowcaseItem
    {
        return ShowcaseItem::findOne(['showcase_id' => $showcaseId, 'performance_id' => $performanceId]);
    }

    /**
     * Элементы витрины, ключ — id спектакля.
     *
     * @param int $showcaseId
     * @return array<int, ShowcaseItem>
     */
    public function itemsByPerformance(int $showcaseId): array
    {
        return ShowcaseItem::find()
            ->andWhere(['showcase_id' => $showcaseId])
            ->indexBy('performance_id')
            ->all();
    }

    /**
     * Наибольший ручной порядок в витрине (0 — не задан ни у кого).
     *
     * @param int $showcaseId
     * @return int
     */
    public function maxSort(int $showcaseId): int
    {
        return (int)ShowcaseItem::find()->andWhere(['showcase_id' => $showcaseId])->max('sort');
    }

    /**
     * Спектакли витрины для админки, с фото и разделом.
     *
     * Витрина по правилу — тот же отбор, что на сайте, но со скрытыми в витрине спектаклями
     * (чтобы их можно было вернуть). Ручная витрина — весь её состав, включая спектакли,
     * которых сайт сейчас не покажет (снят с публикации сам или раздел): админка помечает их.
     *
     * @param Showcase $showcase
     * @param DateRange $range
     * @return Performance[]
     */
    public function preview(Showcase $showcase, DateRange $range): array
    {
        if ($showcase->isManual()) {
            $query = Performance::find()->alias(ShowcaseSelection::ALIAS)
                ->innerJoin(
                    [ShowcaseSelection::ITEM_ALIAS => ShowcaseItem::tableName()],
                    'si.performance_id = p.id AND si.showcase_id = :showcaseId',
                    [':showcaseId' => (int)$showcase->id],
                )
                ->orderBy(['si.sort' => SORT_ASC, 'p.id' => SORT_ASC]);
        } else {
            $query = $this->selection->query($showcase, $range, withHidden: true);
        }

        return $query->with(['images', 'mainImage', 'taxonomy'])->all();
    }

    /**
     * Из переданных — спектакли, которые сайт сейчас показывает.
     *
     * @param int[] $performanceIds
     * @return int[]
     */
    public function publicIds(array $performanceIds): array
    {
        if ($performanceIds === []) {
            return [];
        }
        return array_map('intval', Performance::find()->alias('p')->visible('p')
            ->andWhere(['p.id' => $performanceIds])
            ->select('p.id')
            ->column());
    }

    /**
     * Спектакли, которых ещё нет в витрине: id => название (для добавления в ручную витрину).
     *
     * @param int $showcaseId
     * @return array<int, string>
     */
    public function candidates(int $showcaseId): array
    {
        return Performance::find()
            ->select(['title', 'id'])
            ->andWhere(['not in', 'id', ShowcaseItem::find()->select('performance_id')->andWhere(['showcase_id' => $showcaseId])])
            ->orderBy(['title' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Используется ли раздел витринами (для запрета удаления раздела).
     *
     * @param int $taxonomyId
     * @return bool
     */
    public function existsByTaxonomy(int $taxonomyId): bool
    {
        return Showcase::find()->andWhere(['taxonomy_id' => $taxonomyId])->exists();
    }

    /**
     * @param Showcase $showcase
     * @return void
     * @throws Exception
     */
    public function save(Showcase $showcase): void
    {
        if (!$showcase->save()) {
            throw new RuntimeException('Saving error.');
        }
    }

    /**
     * @param ShowcaseItem $item
     * @return void
     * @throws Exception
     */
    public function saveItem(ShowcaseItem $item): void
    {
        if (!$item->save()) {
            throw new RuntimeException('Saving showcase item error.');
        }
    }

    /**
     * @param Showcase $showcase
     * @return void
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Showcase $showcase): void
    {
        if (!$showcase->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }

    /**
     * @param ShowcaseItem $item
     * @return void
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function removeItem(ShowcaseItem $item): void
    {
        if (!$item->delete()) {
            throw new RuntimeException('Removing showcase item error.');
        }
    }
}
