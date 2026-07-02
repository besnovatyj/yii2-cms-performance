<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;
use yii\db\Exception;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_140015_create_performance_performances_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%performance_performances}}';

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
            'taxonomy_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор категории'),
            'created_at' => $this->dateTime()->null()->defaultExpression('NOW()')
                ->comment('Дата создания'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()')->append('ON UPDATE NOW()')
                ->comment('Дата последнего редактирования'),
            'title' => $this->string(255)->notNull()
                ->comment('Название'),
            'author' => $this->string(255)->null()
                ->comment('Автор'),
            'genre' => $this->text()->null()
                ->comment('Жанр'),
            'description' => $this->text()->null()
                ->comment('Описание'),
            'production_group' => $this->text()->null()
                ->comment('Постановочная группа'),
            'actors' => $this->text()->null()
                ->comment('Занятые актёры'),
            'age_limit' => $this->string(255)->notNull()
                ->comment('Возрастной ценз'),
            'main_image_id' => $this->integer(10)->null()
                ->comment('Идентификатор основной фотографии'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус отображения'),
            'meta_json' => $this->text()->notNull()
                ->comment('JSON meta'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Спектакли');

        $this->createIndexes(static::TABLE_NAME, 'taxonomy_id');
        $this->createIndexes(static::TABLE_NAME, 'main_image_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
