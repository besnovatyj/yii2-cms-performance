<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\image;

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\repositories\PerformanceRepository;
use Besnovatyj\Images\base\BaseImage;
use Besnovatyj\Images\contracts\ImageOwnerInterface;
use yii\db\Exception;

/**
 * Адаптер Performance к ImageOwnerInterface.
 *
 * Реализует pessimistic lock через PessimisticLockBehavior Performance,
 * чтобы исключить race condition при параллельной загрузке изображений
 * (несколько запросов одновременно видят main_image_id = null и пытаются
 * его установить, что приводит к FK constraint violation).
 */
readonly class PerformanceImageOwner implements ImageOwnerInterface
{
    public function __construct(
        private Performance           $performance,
        private PerformanceRepository $repository,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getOwnerId(): int
    {
        return $this->performance->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return Image[]
     */
    public function getOwnedImages(): array
    {
        return $this->performance->images;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageId(): ?int
    {
        return $this->performance->main_image_id ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMainImageId(?int $imageId): void
    {
        $this->performance->setMainImage($imageId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function saveOwner(): void
    {
        $this->repository->save($this->performance);
    }

    /**
     * Блокирует строку галереи (SELECT FOR UPDATE) до конца транзакции.
     *
     * Исключает race condition при параллельной загрузке нескольких файлов.
     * @throws Exception
     */
    public function lockOwner(): void
    {
        $this->performance->lock();
    }

    /**
     * Обновляет данные галереи из БД после применения блокировки.
     */
    public function refreshOwner(): void
    {
        $this->performance->refresh();
    }
}
