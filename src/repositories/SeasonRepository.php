<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\repositories;

use Besnovatyj\Performance\entities\season\Season;
use DateTimeImmutable;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * Репозиторий театральных сезонов (админка).
 */
class SeasonRepository
{
    /**
     * @param int $id
     * @return Season
     */
    public function get(int $id): Season
    {
        if (!$season = Season::findOne($id)) {
            throw new NotFoundException('Season is not found.');
        }
        return $season;
    }

    /**
     * Пересекается ли период с другим сезоном.
     *
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @param int|null $exceptId сезон, который сравнивать не нужно (редактируемый)
     * @return bool
     */
    public function overlaps(DateTimeImmutable $start, DateTimeImmutable $end, ?int $exceptId = null): bool
    {
        return Season::find()
            ->andWhere(['<=', 'start_date', $end->format('Y-m-d')])
            ->andWhere(['>=', 'end_date', $start->format('Y-m-d')])
            ->andFilterWhere(['<>', 'id', $exceptId])
            ->exists();
    }

    /**
     * @param Season $season
     * @return void
     * @throws Exception
     */
    public function save(Season $season): void
    {
        if (!$season->save()) {
            throw new RuntimeException('Saving error.');
        }
    }

    /**
     * @param Season $season
     * @return void
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Season $season): void
    {
        if (!$season->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }
}
