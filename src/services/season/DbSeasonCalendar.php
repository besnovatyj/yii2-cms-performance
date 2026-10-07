<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\season;

use Besnovatyj\Performance\entities\season\Season;
use DateTimeImmutable;

/**
 * Календарь сезонов из таблицы сезонов модуля.
 *
 * Ответы запоминаются на время запроса: несколько витрин на странице спрашивают одни и те же даты.
 */
final class DbSeasonCalendar implements SeasonCalendar
{
    /** @var array<string, SeasonPeriod|null> */
    private array $memo = [];

    /**
     * {@inheritdoc}
     */
    public function seasonOf(DateTimeImmutable $date): ?SeasonPeriod
    {
        $key = $date->format('Y-m-d');
        if (array_key_exists($key, $this->memo)) {
            return $this->memo[$key];
        }

        /** @var Season|null $season */
        $season = Season::find()
            ->andWhere(['<=', 'start_date', $key])
            ->orderBy(['start_date' => SORT_DESC])
            ->limit(1)
            ->one();

        return $this->memo[$key] = $season === null ? null : new SeasonPeriod(
            (string)$season->name,
            new DateTimeImmutable((string)$season->start_date),
            new DateTimeImmutable((string)$season->end_date),
        );
    }
}
