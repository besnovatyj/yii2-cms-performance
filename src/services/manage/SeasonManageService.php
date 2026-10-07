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
        if ($this->seasons->overlaps($form->start(), $form->end(), $exceptId)) {
            throw new DomainException('Сезон пересекается с другим сезоном.');
        }
    }
}
