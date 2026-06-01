<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\controllers\backend;

use Besnovatyj\Performance\forms\backend\TagForm;
use Besnovatyj\Performance\repositories\TagRepository;
use Besnovatyj\Performance\services\manage\TagManageService;
use DomainException;
use Exception;
use Throwable;
use Yii;
use Besnovatyj\Performance\entities\Tag;
use Besnovatyj\Performance\forms\backend\search\TagSearch;

use yii\helpers\VarDumper;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class TagController extends Controller
{
    use \common\components\controller\ControllerTrait;

    private TagManageService $service;
    private TagRepository $repo;

    public function __construct($id, $module, TagManageService $service, TagRepository $repo, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->repo = $repo;
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    public function actionIndex(): string
    {
        $searchModel = new TagSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'tag' => $this->repo->get($id),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $form = new TagForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $tag = $this->service->create($form);
                return $this->redirect(['view', 'id' => $tag->id]);
            } catch (Exception $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @param int $id
     * @return string|Response
     */
    public function actionUpdate(int $id): Response|string
    {
        $tag = $this->repo->get($id);

        $form = new TagForm($tag);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($tag->id, $form);
                return $this->redirect(['view', 'id' => $tag->id]);
            } catch (Exception $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('update', [
            'model' => $form,
            'tag' => $tag,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect(['index']);
    }

    public function actionEmptyTags(): Response|string
    {
        try {
            $dataProvider = $this->service->findEmpty();
            return $this->render('empty-tags',
                ['dataProvider' => $dataProvider]
            );
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect('index');
    }

    public function actionDeleteEmptyTags(): Response|string
    {
        try {
            $count = $this->service->deleteEmpty();
            Yii::$app->session->setFlash('success', '"' . $count . '" tags deleted.');
            return $this->redirect('empty-tags');
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect('empty-tags');
    }

    /**
     * Для виджета Select2Widget
     * @return array
     */
    public function actionSearchEndpoint(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Yii::$app->request->get('q', '');
        $tags = Tag::find()
            ->where(['like', 'name', $query])
            ->select(['id', 'name as text'])
            ->asArray()
            ->all();
        return ['results' => $tags];
    }

}
