<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\entities\performance;

use yii\db\ActiveRecord;

/**
 * @property int $performance_id;
 * @property int $tag_id;
 */
class TagAssignment extends ActiveRecord
{
    public static function create(int $tagId): self
    {
        $assignment = new static();
        $assignment->tag_id = $tagId;
        return $assignment;
    }

    public function isForTag(int $id): bool
    {
        return $this->tag_id === $id;
    }

    public static function tableName(): string
    {
        return '{{%performance_tag_asgmt}}';
    }
}
