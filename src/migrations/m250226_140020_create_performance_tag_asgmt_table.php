<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;
use yii\db\Exception;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_140020_create_performance_tag_asgmt_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%performance_tag_asgmt}}';

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
            'performance_id' => $this->integer()->notNull()
                ->comment('Идентификатор'),
            'tag_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор тега'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Связь сущности с тегом');

        $this->createIndexes(static::TABLE_NAME, 'performance_id');
        $this->createIndexes(static::TABLE_NAME, 'tag_id');
        $this->createIndexes(static::TABLE_NAME, ['performance_id', 'tag_id'], true);

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
