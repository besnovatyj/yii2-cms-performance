<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\season;

use DateTimeImmutable;

/**
 * Границы одного сезона — то, что нужно расчёту периода, без привязки к хранению.
 */
final readonly class SeasonPeriod
{
    /**
     * @param string $name
     * @param DateTimeImmutable $start дата открытия
     * @param DateTimeImmutable $end дата закрытия
     */
    public function __construct(
        public string            $name,
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
    )
    {
    }
}
