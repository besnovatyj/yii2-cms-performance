<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

/**
 * Как задана верхняя граница даты премьеры.
 */
enum ToMode: string
{
    /** По сегодняшний день включительно: объявленные будущие премьеры не попадают. */
    case Today = 'today';

    /** По закрытие текущего сезона: попадают и объявленные премьеры этого сезона. */
    case SeasonEnd = 'season_end';

    /** Без верхней границы. */
    case None = 'none';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Today => 'По сегодняшний день',
            self::SeasonEnd => 'По закрытие текущего сезона',
            self::None => 'Без ограничения',
        };
    }

    /**
     * Значения для выпадающего списка.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(array_map(static fn(self $c): array => [$c->value, $c->label()], self::cases()), 1, 0);
    }
}
