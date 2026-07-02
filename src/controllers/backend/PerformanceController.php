<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\controllers\backend;

use Besnovatyj\Performance\forms\backend\performance\PerformanceForm;
use Besnovatyj\Performance\forms\backend\search\PerformanceSearch;
use Besnovatyj\Performance\image\PerformanceImageOwner;
use Besnovatyj\Performance\repositories\PerformanceRepository;
use Besnovatyj\Performance\services\manage\PerformanceManageService;
use Besnovatyj\Images\helpers\ImageActionsMap;
use Besnovatyj\Performance\entities\performance\Image;
use common\components\controller\ControllerTrait;
use Besnovatyj\Kernel\urlmanager\UrlManagerHelperTrait;
use DomainException;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;

class PerformanceController extends Controller
{
    use ControllerTrait;
    use UrlManagerHelperTrait;

    private PerformanceManageService $service;
    private PerformanceRepository $performancesRepo;

    public function __construct(
        $id,
        $module,
        PerformanceManageService $service,
        PerformanceRepository $performancesRepo,
        $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service          = $service;
        $this->performancesRepo = $performancesRepo;
    }

    /**
     * Регистрирует standalone image-actions через ImageActionsMap.
     *
     * Performance передаёт PerformanceImageOwner, который реализует pessimistic lock
     * (SELECT FOR UPDATE) для исключения race condition при параллельной загрузке.
     *
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return ImageActionsMap::get(
            Image::class,
            fn(int $id) => new PerformanceImageOwner($this->performancesRepo->get($id), $this->performancesRepo),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'activate'       => ['POST'],
                    'draft'          => ['POST'],
                    'delete'         => ['POST'],
                    'add-image'      => ['POST'],
                    'delete-image'   => ['POST'],
                    'set-main-image' => ['POST'],
                    'get-images'     => ['POST'],
                    'set-new-sort'   => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new PerformanceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     * @throws InvalidConfigException
     */
    public function actionView(int $id): Response|string
    {
        try {
            $absoluteFrontendUrl = $this->getAbsoluteFrontendRoute('/Performance/performance/view/', ['id' => $id]);
            return $this->render('view', [
                'performance'         => $this->performancesRepo->get($id),
                'absoluteFrontendUrl' => $absoluteFrontendUrl,
            ]);
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->goHome();
    }

    /**
     * @return Response|string
     */
    public function actionCreate(): Response|string
    {
        $form = new PerformanceForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $performance = $this->service->create($form);
                return $this->redirect(['view', 'id' => $performance->id]);
            } catch (Throwable $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id): Response|string
    {
        $performance = $this->performancesRepo->get($id);
        $form = new PerformanceForm($performance);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($performance->id, $form);
                return $this->redirect(['view', 'id' => $performance->id]);
            } catch (Throwable $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }

        return $this->render('update', [
            'model' => $form,
            'performance' => $performance,
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
            Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
        }
        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionActivate(int $id): Response
    {
        try {
            $this->service->activate($id);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
        }
        return $this->goReferer();
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDraft(int $id): Response
    {
        try {
            $this->service->draft($id);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
        }
        return $this->goReferer();
    }

}
