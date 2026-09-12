<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\controllers\frontend;

use Besnovatyj\Performance\readModels\TaxonomyReadRepository;
use Besnovatyj\Performance\readModels\PerformanceReadRepository;
use Besnovatyj\Tags\readModels\TagReadRepository;

use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PerformanceController extends Controller
{
    private PerformanceReadRepository $performances;
    private TaxonomyReadRepository $taxonomies;
    private TagReadRepository $tags;

    public function __construct(
        $id,
        $module,
        PerformanceReadRepository $performances,
        TaxonomyReadRepository $taxonomies,
        TagReadRepository $tags,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->performances = $performances;
        $this->taxonomies = $taxonomies;
        $this->tags = $tags;
    }

    public function actionIndex(): string
    {
        $dataProvider = $this->performances->getAll();
        $taxonomy = $this->taxonomies->getRoot();

        return $this->render('index', [
            'taxonomy' => $taxonomy,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTaxonomy(string $slug): string
    {
        if (!$taxonomy = $this->taxonomies->findBySlug($slug)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->performances->getAllByTaxonomy($taxonomy);

        return $this->render('taxonomy', [
            'taxonomy' => $taxonomy,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTag(string $slug): string
    {
        if (!$tag = $this->tags->findBySlug($slug)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->performances->getAllByTag($tag);

        return $this->render('tag', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(string $id): string
    {
        if (!$performance = $this->performances->find($id)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('view', [
            'performance' => $performance,
        ]);
    }
}
