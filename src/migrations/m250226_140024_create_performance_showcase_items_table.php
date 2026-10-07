<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_140024_create_performance_showcase_items_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%performance_showcase_items}}';

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
            'showcase_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор витрины'),
            'performance_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор спектакля'),
            'image_id' => $this->integer(10)->null()
                ->comment('Изображение спектакля для витрины (null = главное)'),
            'sort' => $this->integer(10)->null()
                ->comment('Ручной порядок (null = не задан)'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(1)
                ->comment('1 — показывать, 0 — скрыть из витрины'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Элементы витрин спектаклей: состав ручной витрины или поправки к правилу');

        $this->createIndexes(static::TABLE_NAME, ['showcase_id', 'performance_id'], false, true);
        $this->createIndexes(static::TABLE_NAME, 'performance_id');
        $this->createIndexes(static::TABLE_NAME, 'image_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
