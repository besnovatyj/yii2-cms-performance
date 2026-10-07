<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

/**
 * Порядок вывода спектаклей в витрине.
 */
enum Order: string
{
    case PremiereDesc = 'premiere_desc';
    case PremiereAsc = 'premiere_asc';
    case Title = 'title';

    /** Ручной порядок элементов; спектакли без заданного порядка — следом, от свежей премьеры. */
    case Manual = 'manual';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PremiereDesc => 'От новой премьеры к старой',
            self::PremiereAsc => 'От старой премьеры к новой',
            self::Title => 'По названию',
            self::Manual => 'Ручной порядок',
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
