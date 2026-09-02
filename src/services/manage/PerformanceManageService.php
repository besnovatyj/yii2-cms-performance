<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\services\manage;

use Besnovatyj\Meta\Meta;
use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\performance\TagAssignment;
use Besnovatyj\Performance\entities\Tag;
use Besnovatyj\Performance\forms\backend\performance\PerformanceForm;
use Besnovatyj\Performance\repositories\PerformanceRepository;
use Besnovatyj\Performance\repositories\TagRepository;
use Besnovatyj\Performance\repositories\TaxonomyRepository;
use DomainException;
use ReflectionException;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\Inflector;

/**
 * Сервис управления спектаклями.
 *
 * Отвечает за CRUD спектаклей и управление тегами.
 * Логика загрузки/удаления изображений вынесена в standalone actions
 * через пакет besnovatyj/yii2-cms-images + PerformanceImageOwner.
 */
class PerformanceManageService
{
    private PerformanceRepository $performances;
    private TaxonomyRepository $taxonomies;
    private TagRepository $tags;

    public function __construct(
        PerformanceRepository $performances,
        TaxonomyRepository    $taxonomies,
        TagRepository         $tags
    )
    {
        $this->performances = $performances;
        $this->taxonomies = $taxonomies;
        $this->tags = $tags;
    }

    /**
     * @param PerformanceForm $form
     * @return Performance
     * @throws Throwable
     */
    public function create(PerformanceForm $form): Performance
    {
        $taxonomy = $this->taxonomies->get($form->taxonomies->main);

        $performance = Performance::create(
            $taxonomy->id,
            $form->title,
            $form->author,
            $form->genre,
            $form->description,
            $form->production_group,
            $form->actors,
            $form->age_limit,
            $form->premiere_date,
            $form->status,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            )
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->performances->save($performance);
            $this->assignTags($performance, $form->tags->newTagsNames);
            $transaction->commit();
            return $performance;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param int $id
     * @param PerformanceForm $form
     * @throws Throwable
     */
    public function edit(int $id, PerformanceForm $form): void
    {
        $performance = $this->performances->get($id);
        $taxonomy = $this->taxonomies->get($form->taxonomies->main);

        $performance->edit(
            $form->title,
            $form->author,
            $form->genre,
            $form->description,
            $form->production_group,
            $form->actors,
            $form->age_limit,
            $form->premiere_date,
            $form->status,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            )
        );

        $performance->changeMainTaxonomy($taxonomy->id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->performances->save($performance);

            $this->revokeTags($performance);
            $this->assignTags($performance, $form->tags->newTagsNames);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function remove(int $id): void
    {
        $performance = $this->performances->get($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->revokeTags($performance);
            $this->removeImages($performance);

            $this->performances->remove($performance);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function activate(int $id): void
    {
        $performance = $this->performances->get($id);
        $performance->activate();
        $this->performances->save($performance);
    }

    /**
     * @throws Exception
     */
    public function draft(int $id): void
    {
        $performance = $this->performances->get($id);
        $performance->draft();
        $this->performances->save($performance);
    }

    // ==================== Private methods ====================

    /**
     * @throws Exception
     */
    private function assignTags(Performance $performance, array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            $slug = Inflector::slug($tagName);

            $tag = $this->tags->findBySlug($slug);
            if (!$tag) {
                $tag = Tag::create($tagName, $slug);
                $this->tags->save($tag);
            }

            $exists = TagAssignment::find()
                ->andWhere(['performance_id' => $performance->id, 'tag_id' => $tag->id])
                ->exists();

            if ($exists) {
                continue;
            }

            $assignment = new TagAssignment();
            $assignment->performance_id = $performance->id;
            $assignment->tag_id = $tag->id;

            if (!$assignment->save()) {
                throw new Exception('Failed to save tag assignment.');
            }
        }
    }

    /**
     * @param Performance $performance
     */
    private function revokeTags(Performance $performance): void
    {
        TagAssignment::deleteAll(['performance_id' => $performance->id]);
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    private function removeImages(Performance $performance): void
    {
        foreach ($performance->images as $image) {
            $image->delete();
        }
    }

}
