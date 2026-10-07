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
     * Сезон, с которым пересекается период; null — пересечений нет.
     *
     * Отсутствующая дата закрытия — бесконечность: открытый сезон пересекается со всеми,
     * что открываются после него, поэтому открытым может быть только последний сезон.
     *
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable|null $end null — период открыт
     * @param int|null $exceptId сезон, который сравнивать не нужно (редактируемый)
     * @return Season|null
     */
    public function findOverlapping(DateTimeImmutable $start, ?DateTimeImmutable $end, ?int $exceptId = null): ?Season
    {
        $query = Season::find()
            ->andWhere(['or', ['end_date' => null], ['>=', 'end_date', $start->format('Y-m-d')]])
            ->andFilterWhere(['<>', 'id', $exceptId])
            ->orderBy(['start_date' => SORT_ASC])
            ->limit(1);
        if ($end !== null) {
            $query->andWhere(['<=', 'start_date', $end->format('Y-m-d')]);
        }

        return $query->one();
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
