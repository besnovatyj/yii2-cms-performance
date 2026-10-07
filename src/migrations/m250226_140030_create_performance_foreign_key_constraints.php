<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use Yii;
use yii\db\Exception;

class m250226_140030_create_performance_foreign_key_constraints extends BaseMigration
{

    /**
     * @throws Exception
     */
    public function safeUp(): void
    {
        parent::safeUp();

        Yii::$app->getDb()->createCommand("SET foreign_key_checks = 0")->execute();

        // Изображения
        $this->createFKs(
            m250226_140010_create_performance_images_table::TABLE_NAME,
            'performance_id',
            m250226_140015_create_performance_performances_table::TABLE_NAME,
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Спектакли
        $this->createFKs(
            m250226_140015_create_performance_performances_table::TABLE_NAME,
            'taxonomy_id',
            m250226_140000_create_performance_taxonomies_table::TABLE_NAME,
            'id',
        );
        $this->createFKs(
            m250226_140015_create_performance_performances_table::TABLE_NAME,
            'main_image_id',
            m250226_140010_create_performance_images_table::TABLE_NAME,
            'id',
            'SET NULL',
        );

        // Теги — в общем модуле Tags (полиморфная таблица связей без FK на спектакли), здесь их больше нет.

        // Витрины. Раздел витрины без каскада: удаление раздела, на который ссылается витрина,
        // запрещено (иначе витрина молча расширилась бы до всей афиши).
        $this->createFKs(
            m250226_140022_create_performance_showcases_table::TABLE_NAME,
            'taxonomy_id',
            m250226_140000_create_performance_taxonomies_table::TABLE_NAME,
            'id',
        );
        $this->createFKs(
            m250226_140024_create_performance_showcase_items_table::TABLE_NAME,
            'showcase_id',
            m250226_140022_create_performance_showcases_table::TABLE_NAME,
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createFKs(
            m250226_140024_create_performance_showcase_items_table::TABLE_NAME,
            'performance_id',
            m250226_140015_create_performance_performances_table::TABLE_NAME,
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createFKs(
            m250226_140024_create_performance_showcase_items_table::TABLE_NAME,
            'image_id',
            m250226_140010_create_performance_images_table::TABLE_NAME,
            'id',
            'SET NULL',
        );

        Yii::$app->db->createCommand('SET foreign_key_checks = 1')->execute();

    }

    public function safeDown(): void
    {
        // Отменяем действия по умолчанию,
        // так как \Besnovatyj\Kernel\migration\BaseMigration::safeDown() вызывает static::TABLE_NAME,
        // которого в данной миграции не существует.
        // Так же, \Besnovatyj\Kernel\migration\BaseMigration::safeDown() при удалении таблиц сам удалит у них все индексы и внешние ключи.

        // parent::safeDown();
    }

}
