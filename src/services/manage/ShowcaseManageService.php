<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\manage;

use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\ShowcaseItem;
use Besnovatyj\Performance\forms\backend\showcase\ShowcaseForm;
use Besnovatyj\Performance\repositories\NotFoundException;
use Besnovatyj\Performance\repositories\PerformanceRepository;
use Besnovatyj\Performance\repositories\ShowcaseRepository;
use DomainException;
use Throwable;
use yii\db\Exception;

/**
 * Сервис управления витринами спектаклей.
 *
 * Элементы адресуются парой «витрина + спектакль», а не id элемента: у витрины по правилу
 * элемента может ещё не быть — он заводится при первой поправке и удаляется, когда поправок
 * не осталось.
 */
class ShowcaseManageService
{
    public function __construct(
        private readonly ShowcaseRepository    $showcases,
        private readonly PerformanceRepository $performances,
    )
    {
    }

    /**
     * @param ShowcaseForm $form
     * @return Showcase
     * @throws Exception
     */
    public function create(ShowcaseForm $form): Showcase
    {
        $showcase = Showcase::create((string)$form->code, (string)$form->name, $form->sort, $form->rule());
        $this->showcases->save($showcase);
        return $showcase;
    }

    /**
     * @param int $id
     * @param ShowcaseForm $form
     * @return void
     * @throws Exception
     */
    public function edit(int $id, ShowcaseForm $form): void
    {
        $showcase = $this->showcases->get($id);
        $showcase->edit((string)$form->code, (string)$form->name, $form->sort, $form->rule());
        $this->showcases->save($showcase);
    }

    /**
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function activate(int $id): void
    {
        $showcase = $this->showcases->get($id);
        $showcase->activate();
        $this->showcases->save($showcase);
    }

    /**
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function draft(int $id): void
    {
        $showcase = $this->showcases->get($id);
        $showcase->draft();
        $this->showcases->save($showcase);
    }

    /**
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function remove(int $id): void
    {
        $this->showcases->remove($this->showcases->get($id));
    }

    /**
     * Добавить спектакль в ручную витрину (в конец ручного порядка).
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @return void
     * @throws Exception
     */
    public function addItem(int $showcaseId, int $performanceId): void
    {
        $showcase = $this->showcases->get($showcaseId);
        if (!$showcase->isManual()) {
            throw new DomainException('Добавлять спектакли вручную можно только в ручную витрину.');
        }
        $this->performances->get($performanceId);
        if ($this->showcases->findItem($showcaseId, $performanceId) !== null) {
            throw new DomainException('Спектакль уже есть в витрине.');
        }

        $item = ShowcaseItem::create($showcaseId, $performanceId, $this->showcases->maxSort($showcaseId) + 1);
        $this->showcases->saveItem($item);
    }

    /**
     * Убрать спектакль из ручной витрины или сбросить поправки витрины по правилу.
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @return void
     * @throws Throwable
     */
    public function removeItem(int $showcaseId, int $performanceId): void
    {
        $item = $this->showcases->findItem($showcaseId, $performanceId);
        if ($item !== null) {
            $this->showcases->removeItem($item);
        }
    }

    /**
     * Выбрать фото спектакля для витрины; null — главное фото.
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @param int|null $imageId
     * @return void
     * @throws Throwable
     */
    public function chooseImage(int $showcaseId, int $performanceId, ?int $imageId): void
    {
        $image = null;
        if ($imageId !== null) {
            $image = Image::findOne($imageId) ?? throw new NotFoundException('Image is not found.');
        }

        $item = $this->itemFor($showcaseId, $performanceId);
        $item->chooseImage($image);
        $this->store($item);
    }

    /**
     * Показать или скрыть спектакль в витрине.
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @return void
     * @throws Throwable
     */
    public function toggleItem(int $showcaseId, int $performanceId): void
    {
        $item = $this->itemFor($showcaseId, $performanceId);
        $item->toggle();
        $this->store($item);
    }

    /**
     * Задать ручной порядок спектакля; null — снять.
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @param int|null $sort
     * @return void
     * @throws Throwable
     */
    public function moveItem(int $showcaseId, int $performanceId, ?int $sort): void
    {
        $item = $this->itemFor($showcaseId, $performanceId);
        $item->moveTo($sort);
        $this->store($item);
    }

    /**
     * Элемент витрины для поправки: существующий или новый (только у витрины по правилу —
     * в ручную витрину спектакль попадает через {@see addItem()}).
     *
     * @param int $showcaseId
     * @param int $performanceId
     * @return ShowcaseItem
     */
    private function itemFor(int $showcaseId, int $performanceId): ShowcaseItem
    {
        $item = $this->showcases->findItem($showcaseId, $performanceId);
        if ($item !== null) {
            return $item;
        }

        $showcase = $this->showcases->get($showcaseId);
        if ($showcase->isManual()) {
            throw new DomainException('Спектакля нет в витрине.');
        }
        $this->performances->get($performanceId);

        return ShowcaseItem::create($showcaseId, $performanceId);
    }

    /**
     * Сохраняет элемент; у витрины по правилу элемент без поправок удаляется.
     *
     * @param ShowcaseItem $item
     * @return void
     * @throws Throwable
     */
    private function store(ShowcaseItem $item): void
    {
        if ($item->isNeutral() && !$this->showcases->get((int)$item->showcase_id)->isManual()) {
            if (!$item->getIsNewRecord()) {
                $this->showcases->removeItem($item);
            }
            return;
        }
        $this->showcases->saveItem($item);
    }
}
