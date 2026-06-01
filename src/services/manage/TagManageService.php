<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\services\manage;

use Besnovatyj\Performance\entities\Tag;
use Besnovatyj\Performance\forms\backend\TagForm;
use Besnovatyj\Performance\repositories\TagRepository;
use Throwable;
use yii\data\DataProviderInterface;
use yii\db\Exception;
use yii\db\StaleObjectException;

class TagManageService
{
    private TagRepository $tags;

    public function __construct(TagRepository $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @throws Exception
     */
    public function create(TagForm $form): Tag
    {
        $tag = Tag::create(
            $form->name,
            $form->slug,
        );
        $this->tags->save($tag);
        return $tag;
    }

    /**
     * @throws Exception
     */
    public function edit($id, TagForm $form): void
    {
        $tag = $this->tags->get($id);
        $tag->edit(
            $form->name,
            $form->slug,
        );
        $this->tags->save($tag);
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove($id): void
    {
        $tag = $this->tags->get($id);
        $this->tags->remove($tag);
    }

    public function findEmpty(): DataProviderInterface
    {
        return $this->tags->searchEmptyTags();
    }

    public function deleteEmpty(): int
    {
        return $this->tags->deleteEmptyTags();
    }
}
