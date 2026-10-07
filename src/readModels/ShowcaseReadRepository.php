<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\readModels;

use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\ShowcaseItem;
use Besnovatyj\Performance\readModels\views\ShowcaseCard;
use Besnovatyj\Performance\services\showcase\PeriodResolver;
use Besnovatyj\Performance\services\showcase\ShowcaseSelection;
use DateTimeImmutable;

/**
 * Витрины спектаклей для фронтенда: только активные витрины и только публичные спектакли.
 */
class ShowcaseReadRepository
{
    public function __construct(
        private readonly PeriodResolver    $periods,
        private readonly ShowcaseSelection $selection,
    )
    {
    }

    /**
     * Активная витрина по коду.
     *
     * @param string $code
     * @return Showcase|null
     */
    public function findByCode(string $code): ?Showcase
    {
        return Showcase::find()
            ->andWhere(['code' => $code, 'status' => Showcase::STATUS_ACTIVE])
            ->one();
    }

    /**
     * Карточки витрины на заданный день.
     *
     * @param Showcase $showcase
     * @param DateTimeImmutable $today от него считаются относительные границы периода
     * @param int|null $limit сколько карточек; null — сколько задано в витрине
     * @return ShowcaseCard[]
     */
    public function cards(Showcase $showcase, DateTimeImmutable $today, ?int $limit = null): array
    {
        $rule = $showcase->rule();
        $range = $this->periods->resolve($rule, $today);
        if (!$showcase->isManual() && $range->isEmpty()) {
            return [];
        }

        $query = $this->selection->query($showcase, $range)->with(['mainImage', 'taxonomy']);
        $limit ??= $rule->limit;
        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        /** @var Performance[] $performances */
        $performances = $query->all();
        if ($performances === []) {
            return [];
        }

        $chosen = $this->chosenImages($showcase, $performances);

        return array_map(
            static fn(Performance $p): ShowcaseCard => new ShowcaseCard($p, $chosen[(int)$p->id] ?? $p->mainImage),
            $performances,
        );
    }

    /**
     * Карточки активной витрины по коду; нет такой витрины — пустой массив.
     *
     * @param string $code
     * @param DateTimeImmutable $today
     * @param int|null $limit
     * @return ShowcaseCard[]
     */
    public function cardsByCode(string $code, DateTimeImmutable $today, ?int $limit = null): array
    {
        $showcase = $this->findByCode($code);
        return $showcase === null ? [] : $this->cards($showcase, $today, $limit);
    }

    /**
     * Фото, выбранные в витрине, одним запросом; ключ — id спектакля.
     *
     * @param Showcase $showcase
     * @param Performance[] $performances
     * @return array<int, Image>
     */
    private function chosenImages(Showcase $showcase, array $performances): array
    {
        $ids = array_map(static fn(Performance $p): int => (int)$p->id, $performances);

        $imageIds = ShowcaseItem::find()
            ->select('image_id')
            ->andWhere(['showcase_id' => $showcase->id, 'performance_id' => $ids])
            ->andWhere(['not', ['image_id' => null]])
            ->column();
        if ($imageIds === []) {
            return [];
        }

        $chosen = [];
        /** @var Image $image */
        foreach (Image::find()->andWhere(['id' => $imageIds, 'performance_id' => $ids])->all() as $image) {
            $chosen[(int)$image->performance_id] = $image;
        }
        return $chosen;
    }
}
