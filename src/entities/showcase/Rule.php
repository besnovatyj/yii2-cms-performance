<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

use DateTimeImmutable;
use DomainException;

/**
 * Правило отбора и вывода спектаклей витрины.
 *
 * Фильтры (раздел, период премьеры) действуют только при {@see Source::Rule}; у ручной витрины
 * состав задают её элементы, а из правила берутся только порядок и количество.
 *
 * Период описан границами, а не готовыми датами: относительная граница пересчитывается от
 * сегодняшнего дня при каждом показе (см. {@see \Besnovatyj\Performance\services\showcase\PeriodResolver}).
 */
final readonly class Rule
{
    /**
     * @param Source $source откуда берутся спектакли
     * @param int|null $taxonomyId раздел афиши; null — любой
     * @param bool $withDescendants учитывать подразделы раздела
     * @param FromMode $fromMode как задана нижняя граница премьеры
     * @param DateTimeImmutable|null $fromDate нижняя граница для {@see FromMode::Fixed}
     * @param int $fromOffsetMonths на сколько месяцев назад от сегодня для {@see FromMode::Relative}
     * @param Snap $fromSnap выравнивание относительной границы
     * @param ToMode $toMode как задана верхняя граница премьеры
     * @param Order $order порядок вывода
     * @param int|null $limit сколько спектаклей выводить; null — все
     */
    public function __construct(
        public Source             $source = Source::Rule,
        public ?int               $taxonomyId = null,
        public bool               $withDescendants = true,
        public FromMode           $fromMode = FromMode::None,
        public ?DateTimeImmutable $fromDate = null,
        public int                $fromOffsetMonths = 12,
        public Snap               $fromSnap = Snap::None,
        public ToMode             $toMode = ToMode::Today,
        public Order              $order = Order::PremiereDesc,
        public ?int               $limit = null,
    )
    {
        if ($fromMode === FromMode::Fixed && $fromDate === null) {
            throw new DomainException('Для границы «с конкретной даты» нужна дата.');
        }
        if ($fromOffsetMonths < 0) {
            throw new DomainException('Сдвиг границы не может быть отрицательным.');
        }
        if ($limit !== null && $limit < 1) {
            throw new DomainException('Количество спектаклей должно быть положительным.');
        }
    }

    /**
     * Зависит ли период от сезонов (нужна ли таблица сезонов для расчёта).
     *
     * @return bool
     */
    public function usesSeasons(): bool
    {
        return ($this->fromMode === FromMode::Relative && $this->fromSnap === Snap::Season)
            || $this->toMode === ToMode::SeasonEnd;
    }
}
