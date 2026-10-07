<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_140020_create_performance_seasons_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%performance_seasons}}';

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
            'name' => $this->string(64)->notNull()
                ->comment('Название сезона'),
            'start_date' => $this->date()->notNull()
                ->comment('Дата открытия сезона'),
            'end_date' => $this->date()->null()
                ->comment('Дата закрытия сезона (null = сезон открыт, дата ещё не назначена)'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Театральные сезоны');

        $this->createIndexes(static::TABLE_NAME, 'start_date', false, true);

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
