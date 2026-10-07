<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\showcase;

use Besnovatyj\Performance\entities\showcase\FromMode;
use Besnovatyj\Performance\entities\showcase\Rule;
use Besnovatyj\Performance\entities\showcase\Snap;
use Besnovatyj\Performance\entities\showcase\ToMode;
use Besnovatyj\Performance\services\season\SeasonCalendar;
use DateTimeImmutable;

/**
 * Превращает правило витрины в конкретный период премьеры на заданный день.
 *
 * «Сегодня» передаётся снаружи, а не берётся из часов: расчёт детерминирован, его можно
 * проверить на любую дату, а часовой пояс решает вызывающий.
 *
 * Примеры на 07.10.2026 (сезоны открываются в сентябре):
 * - 12 месяцев, без выравнивания, по сегодня — 07.10.2025 … 07.10.2026;
 * - 12 месяцев, к открытию сезона — с открытия сезона 2025/26;
 * - 0 месяцев, к открытию сезона, по закрытие сезона — текущий сезон целиком, с анонсами.
 */
final readonly class PeriodResolver
{
    public function __construct(private SeasonCalendar $seasons)
    {
    }

    /**
     * @param Rule $rule
     * @param DateTimeImmutable $today
     * @return DateRange
     */
    public function resolve(Rule $rule, DateTimeImmutable $today): DateRange
    {
        $today = $today->setTime(0, 0);
        $warnings = [];

        $from = match ($rule->fromMode) {
            FromMode::None => null,
            FromMode::Fixed => $rule->fromDate?->setTime(0, 0),
            FromMode::Relative => $this->snap(
                $this->monthsBefore($today, $rule->fromOffsetMonths),
                $rule->fromSnap,
                $warnings,
            ),
        };

        $to = match ($rule->toMode) {
            ToMode::None => null,
            ToMode::Today => $today,
            ToMode::SeasonEnd => $this->seasonEnd($today, $warnings),
        };

        return new DateRange($from, $to, $warnings);
    }

    /**
     * Выравнивает границу назад, к началу года или сезона.
     *
     * @param DateTimeImmutable $date
     * @param Snap $snap
     * @param string[] $warnings
     * @return DateTimeImmutable
     */
    private function snap(DateTimeImmutable $date, Snap $snap, array &$warnings): DateTimeImmutable
    {
        if ($snap === Snap::Year) {
            return $date->setDate((int)$date->format('Y'), 1, 1);
        }

        if ($snap === Snap::Season) {
            $season = $this->seasons->seasonOf($date);
            if ($season !== null) {
                return $season->start;
            }
            $warnings[] = 'Нет сезона, открывшегося не позже ' . $date->format('d.m.Y')
                . ': граница не выровнена. Добавьте сезон.';
        }

        return $date;
    }

    /**
     * Закрытие текущего сезона; без сезонов — сегодняшний день.
     *
     * @param DateTimeImmutable $today
     * @param string[] $warnings
     * @return DateTimeImmutable
     */
    private function seasonEnd(DateTimeImmutable $today, array &$warnings): DateTimeImmutable
    {
        $season = $this->seasons->seasonOf($today);
        if ($season !== null) {
            return $season->end;
        }

        $warnings[] = 'Нет текущего сезона: верхняя граница — сегодняшний день. Добавьте сезон.';
        return $today;
    }

    /**
     * Та же дата N месяцев назад; несуществующий день прижимается к концу месяца
     * (29.02 → 28.02), а не переползает в следующий, как у `modify('-N months')`.
     *
     * @param DateTimeImmutable $date
     * @param int $months
     * @return DateTimeImmutable
     */
    private function monthsBefore(DateTimeImmutable $date, int $months): DateTimeImmutable
    {
        $total = (int)$date->format('Y') * 12 + (int)$date->format('n') - 1 - $months;
        $year = intdiv($total, 12);
        $month = $total % 12 + 1;

        $first = $date->setDate($year, $month, 1);
        $day = min((int)$date->format('j'), (int)$first->format('t'));

        return $first->setDate($year, $month, $day);
    }
}
