<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\showcase;

use DateTimeImmutable;

/**
 * Рассчитанный период премьеры: включительные границы, любая может отсутствовать.
 *
 * Предупреждения объясняют, где расчёт отступил от правила (например, нет нужного сезона) —
 * их показывает админка, фронт их не видит.
 */
final readonly class DateRange
{
    /**
     * @param DateTimeImmutable|null $from
     * @param DateTimeImmutable|null $to
     * @param string[] $warnings
     */
    public function __construct(
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public array              $warnings = [],
    )
    {
    }

    /**
     * Период пуст: нижняя граница позже верхней — под него не попадёт ни один спектакль.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->from !== null && $this->to !== null && $this->from > $this->to;
    }

    /**
     * Ограничен ли период хотя бы с одной стороны.
     *
     * @return bool
     */
    public function isBounded(): bool
    {
        return $this->from !== null || $this->to !== null;
    }
}
