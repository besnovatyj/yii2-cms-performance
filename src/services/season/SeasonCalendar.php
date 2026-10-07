<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\season;

use DateTimeImmutable;

/**
 * Календарь театральных сезонов для расчёта периодов витрин.
 *
 * Отделён от хранения: расчёт периода не знает, откуда берутся даты сезонов.
 */
interface SeasonCalendar
{
    /**
     * Сезон, к которому относится дата: последний сезон, открывшийся не позже неё.
     *
     * Дата в межсезонье относится к закрывшемуся сезону: до открытия следующего «текущим»
     * остаётся прошедший.
     *
     * @param DateTimeImmutable $date
     * @return SeasonPeriod|null null — ни один сезон до этой даты не открывался
     */
    public function seasonOf(DateTimeImmutable $date): ?SeasonPeriod;
}
