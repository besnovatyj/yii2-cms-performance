<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\entities\showcase;

use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\performance\Performance;
use DomainException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Элемент витрины.
 *
 * У ручной витрины элемент — член её состава. У витрины по правилу — поправка к отбору:
 * какое фото показать, скрыть ли спектакль, на каком месте его вывести. Элемент без поправок
 * для витрины по правилу не нужен и удаляется («сбросить настройки»).
 *
 * Фото хранится ссылкой на изображение, а не позицией в галерее: позиция съезжает при
 * пересортировке галереи. Удалили изображение — ссылка обнуляется (FK SET NULL) и
 * показывается главное фото.
 *
 * @property int $id
 * @property int $showcase_id
 * @property int $performance_id
 * @property int|null $image_id
 * @property int|null $sort
 * @property int $status
 *
 * @property Showcase $showcase
 * @property Performance $performance
 * @property Image|null $image
 */
class ShowcaseItem extends ActiveRecord
{
    public const int STATUS_HIDDEN = 0;
    public const int STATUS_ACTIVE = 1;

    /**
     * @param int $showcaseId
     * @param int $performanceId
     * @param int|null $sort
     * @return self
     */
    public static function create(int $showcaseId, int $performanceId, ?int $sort = null): self
    {
        $item = new static();
        $item->showcase_id = $showcaseId;
        $item->performance_id = $performanceId;
        $item->sort = $sort;
        $item->status = self::STATUS_ACTIVE;
        return $item;
    }

    /**
     * Выбрать фото спектакля для витрины; null — главное фото.
     *
     * @param Image|null $image
     * @return void
     */
    public function chooseImage(?Image $image): void
    {
        if ($image !== null && (int)$image->performance_id !== (int)$this->performance_id) {
            throw new DomainException('Изображение принадлежит другому спектаклю.');
        }
        $this->image_id = $image?->id;
    }

    /**
     * @param int|null $sort
     * @return void
     */
    public function moveTo(?int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return void
     */
    public function toggle(): void
    {
        $this->status = $this->isActive() ? self::STATUS_HIDDEN : self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (int)$this->status === self::STATUS_ACTIVE;
    }

    /**
     * Ничего не меняет в отборе по правилу (можно удалить без потерь).
     *
     * @return bool
     */
    public function isNeutral(): bool
    {
        return $this->isActive() && $this->image_id === null && $this->sort === null;
    }

    // <editor-fold desc="Relations">

    /**
     * @return ActiveQuery
     */
    public function getShowcase(): ActiveQuery
    {
        return $this->hasOne(Showcase::class, ['id' => 'showcase_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPerformance(): ActiveQuery
    {
        return $this->hasOne(Performance::class, ['id' => 'performance_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getImage(): ActiveQuery
    {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    // </editor-fold>

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%performance_showcase_items}}';
    }
}
