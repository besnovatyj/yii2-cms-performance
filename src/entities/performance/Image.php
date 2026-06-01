<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\performance;

use Besnovatyj\Images\base\BaseImage;

/**
 * Изображение спектакля.
 *
 * @property int $id
 * @property int $performance_id
 * @property string $file
 * @property int $sort
 */
class Image extends BaseImage
{
    /**
     * {@inheritdoc}
     */
    protected static function getParentAttribute(): string
    {
        return 'performance_id';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getStorageName(): string
    {
        return 'Performance';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getThumbProfiles(): array
    {
        return [
            'admin'          => ['width' => 70,   'height' => 100], // /backend/gallery/index
            'thumb'          => ['width' => 640,  'height' => 480], // /backend/gallery/view
            'frontend_list' => ['width' => 1200, 'height' => 600], // /frontend/performance/index
            'frontend_item' => ['width' => 1200, 'height' => 600], // /frontend/performance/view
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%performance_images}}';
    }

}
