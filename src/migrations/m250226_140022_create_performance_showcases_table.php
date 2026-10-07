<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<n>' */
class m250226_140022_create_performance_showcases_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%performance_showcases}}';

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
            'code' => $this->string(64)->notNull()
                ->comment('Уникальный код витрины для вызова из темы и шорткода'),
            'name' => $this->string(255)->notNull()
                ->comment('Название витрины'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус витрины'),
            'sort' => $this->integer(10)->notNull()->defaultValue(0)
                ->comment('Сортировка в списке витрин'),
            'source' => $this->string(16)->notNull()->defaultValue('rule')
                ->comment('Откуда берутся спектакли: rule — по правилу, manual — ручной список'),
            'taxonomy_id' => $this->integer(10)->null()
                ->comment('Раздел афиши (null = любой)'),
            'with_descendants' => $this->boolean()->notNull()->defaultValue(true)
                ->comment('Учитывать подразделы раздела'),
            'from_mode' => $this->string(16)->notNull()->defaultValue('none')
                ->comment('Нижняя граница премьеры: none | fixed | relative'),
            'from_date' => $this->date()->null()
                ->comment('Нижняя граница для from_mode = fixed'),
            'from_offset_months' => $this->integer(10)->notNull()->defaultValue(12)
                ->comment('Сколько месяцев назад от сегодня для from_mode = relative'),
            'from_snap' => $this->string(16)->notNull()->defaultValue('none')
                ->comment('Выравнивание относительной границы: none | year | season'),
            'to_mode' => $this->string(16)->notNull()->defaultValue('today')
                ->comment('Верхняя граница премьеры: today | season_end | none'),
            'order_by' => $this->string(32)->notNull()->defaultValue('premiere_desc')
                ->comment('Порядок вывода'),
            'items_limit' => $this->integer(10)->null()
                ->comment('Сколько спектаклей выводить (null = все)'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Витрины спектаклей');

        $this->createIndexes(static::TABLE_NAME, 'code', false, true);
        $this->createIndexes(static::TABLE_NAME, 'status');
        $this->createIndexes(static::TABLE_NAME, 'taxonomy_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
