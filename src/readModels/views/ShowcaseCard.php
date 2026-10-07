<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\readModels\views;

use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\performance\Performance;

/**
 * Карточка витрины: спектакль и фото, выбранное для этой витрины (или главное).
 */
final readonly class ShowcaseCard
{
    /**
     * @param Performance $performance
     * @param Image|null $image null — у спектакля нет изображений
     */
    public function __construct(
        public Performance $performance,
        public ?Image      $image,
    )
    {
    }
}
