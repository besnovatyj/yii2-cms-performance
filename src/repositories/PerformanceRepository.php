<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\repositories;

use Besnovatyj\Performance\entities\performance\Performance;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class PerformanceRepository
{

    public function get(int $id): Performance
    {
        if (!$performances = Performance::findOne($id)) {
            throw new NotFoundException('Performance is not found.');
        }
        return $performances;
    }

    public function existsByMainTaxonomy(int $id): bool
    {
        return Performance::find()->andWhere(['taxonomy_id' => $id])->exists();
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function save(Performance $performance)
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                if ($performance->save()) {
                    return true;
                }
                throw new RuntimeException('Failed to save performance.');
            } catch (Exception $e) {
                if ($e->errorInfo[1] == 1213) { // Код ошибки дедлока
                    $retryCount++;
                    if ($retryCount >= $maxRetries) {
                        throw $e; // Превышено количество попыток
                    }
                    usleep(rand(100, 500) * 1000); // Задержка 100-500 мс
                    continue;
                }
                throw $e; // Другие ошибки
            }
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Performance $performances): void
    {
        if (!$performances->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }
}
