<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\controllers\backend;

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\Performance\forms\backend\TaxonomyForm;
use Besnovatyj\TreeManager\Manager\controllers\TreeController;
use Besnovatyj\TreeManager\Manager\services\TreeServiceInterface;
use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

class TaxonomyController extends TreeController
{
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function __construct($id, $module, $config = [])
    {
        /** @var $treeManager TreeServiceInterface */
        $treeManager = Yii::$container->get('performance.tree.manager');
        $this->treeManager = $treeManager;
        $this->dataSource = new TreeDataSource(
            Taxonomy::class,
            function (Taxonomy $model) {
                return [
                    'id' => $model->id,
                    'title' => $model->name,
                    'slug' => $model->slug,
                ];
            },
            'sort_order'
        );
        $this->createFormClass = TaxonomyForm::class;
        $this->updateFormClass = TaxonomyForm::class;
        $this->formView = '_form';
        $this->indexTitle = 'Управление категориями';
        parent::__construct($id, $module, $config);
    }
}
