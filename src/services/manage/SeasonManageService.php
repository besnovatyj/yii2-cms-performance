<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\manage;

use Besnovatyj\Performance\entities\season\Season;
use Besnovatyj\Performance\forms\backend\season\SeasonForm;
use Besnovatyj\Performance\repositories\SeasonRepository;
use DomainException;
use Throwable;
use yii\db\Exception;

/**
 * Сервис управления театральными сезонами.
 *
 * Сезоны не пересекаются: иначе «сезон, к которому относится дата», был бы неоднозначен.
 * Сезон без даты закрытия открыт до бесконечности, поэтому открытым может быть только последний.
 */
class SeasonManageService
{
    public function __construct(private readonly SeasonRepository $seasons)
    {
    }

    /**
     * @param SeasonForm $form
     * @return Season
     * @throws Exception
     */
    public function create(SeasonForm $form): Season
    {
        $this->guardOverlap($form, null);
        $season = Season::create((string)$form->name, $form->start(), $form->end());
        $this->seasons->save($season);
        return $season;
    }

    /**
     * @param int $id
     * @param SeasonForm $form
     * @return void
     * @throws Exception
     */
    public function edit(int $id, SeasonForm $form): void
    {
        $season = $this->seasons->get($id);
        $this->guardOverlap($form, $id);
        $season->edit((string)$form->name, $form->start(), $form->end());
        $this->seasons->save($season);
    }

    /**
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function remove(int $id): void
    {
        $this->seasons->remove($this->seasons->get($id));
    }

    /**
     * @param SeasonForm $form
     * @param int|null $exceptId
     * @return void
     */
    private function guardOverlap(SeasonForm $form, ?int $exceptId): void
    {
        $other = $this->seasons->findOverlapping($form->start(), $form->end(), $exceptId);
        if ($other === null) {
            return;
        }

        if ($other->isOpen() && $other->start_date < $form->start()->format('Y-m-d')) {
            throw new DomainException('Сезон «' . $other->name . '» ещё открыт: сначала задайте дату его закрытия.');
        }
        if ($form->end() === null) {
            throw new DomainException('Открытым может быть только последний сезон: после этого уже есть сезон «' . $other->name . '». Задайте дату закрытия.');
        }
        throw new DomainException('Сезон пересекается с сезоном «' . $other->name . '».');
    }
}
