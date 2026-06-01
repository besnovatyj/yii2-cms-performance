<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;
use yii\db\Exception;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_140010_create_performance_images_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%performance_images}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'performance_id' => $this->integer(36)->notNull()
                ->comment('Id спектакля'),
            'file' => $this->string(255)->notNull()
                ->comment('Файл фотографии'),
            'sort' => $this->integer(10)->notNull()
                ->comment('Сортировка фотографии у конкретной сущности'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Изображения');

        $this->createIndexes(static::TABLE_NAME, 'performance_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
