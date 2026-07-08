<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\forms\backend\performance;

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Model;

class TaxonomiesForm extends Model
{
    public int|null $main = null;

    public function __construct(?Performance $performances = null, $config = [])
    {
        if ($performances) {
            $this->main = $performances->taxonomy_id;
        }
        parent::__construct($config);
    }

    public function taxonomiesList(): array
    {
        $scope = new TreeQueryScope(Taxonomy::class);
        return $scope->dropdownTree();
    }

    public function rules(): array
    {
        return [
            ['main', 'required'],
            ['main', 'integer'],
        ];
    }
}
