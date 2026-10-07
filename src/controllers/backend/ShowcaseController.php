<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Performance\controllers\backend;

use Besnovatyj\Kernel\controller\ControllerTrait;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\Source;
use Besnovatyj\Performance\forms\backend\showcase\ShowcaseForm;
use Besnovatyj\Performance\repositories\ShowcaseRepository;
use Besnovatyj\Performance\services\manage\ShowcaseManageService;
use Besnovatyj\Performance\services\showcase\PeriodResolver;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Витрины спектаклей.
 *
 * Состав витрины правится HTMX-запросами: каждый экшен элемента возвращает перерисованный
 * список (`_items`) целиком — от одной поправки меняются позиции и отсечка по количеству у
 * соседних строк. Ошибки предметной области отдаются как 400 с текстом — их показывает
 * глобальный обработчик ошибок htmx админки.
 */
class ShowcaseController extends Controller
{
    use ControllerTrait;

    public function __construct(
        $id,
        $module,
        private readonly ShowcaseManageService $service,
        private readonly ShowcaseRepository    $repository,
        private readonly PeriodResolver        $periods,
        $config = [],
    )
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'activate' => ['POST'],
                    'draft' => ['POST'],
                    'period' => ['POST'],
                    'add-item' => ['POST'],
                    'remove-item' => ['POST'],
                    'choose-image' => ['POST'],
                    'toggle-item' => ['POST'],
                    'move-item' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Showcase::find()->with('taxonomy')->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Витрина: правило, рассчитанный период и состав с поправками.
     *
     * @param int $id
     * @return string
     */
    public function actionView(int $id): string
    {
        $showcase = $this->repository->get($id);

        return $this->render('view', [
            'showcase' => $showcase,
            'itemsHtml' => $this->renderItems($showcase),
        ]);
    }

    /**
     * @return Response|string
     */
    public function actionCreate(): Response|string
    {
        $form = new ShowcaseForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $showcase = $this->service->create($form);
                return $this->redirect(['view', 'id' => $showcase->id]);
            } catch (DomainException $e) {
                $form->addError('name', $e->getMessage());
            } catch (Exception $e) {
                $this->handleDomainException($e);
            }
        }

        return $this->render('create', [
            'model' => $form,
            'periodHtml' => $this->renderPeriod($form),
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id): Response|string
    {
        $showcase = $this->repository->get($id);
        $form = new ShowcaseForm($showcase);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($showcase->id, $form);
                return $this->redirect(['view', 'id' => $showcase->id]);
            } catch (DomainException $e) {
                $form->addError('name', $e->getMessage());
            } catch (Exception $e) {
                $this->handleDomainException($e);
            }
        }

        return $this->render('update', [
            'model' => $form,
            'showcase' => $showcase,
            'periodHtml' => $this->renderPeriod($form),
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
        return $this->redirect(['view', 'id' => $id]);
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
        return $this->redirect(['view', 'id' => $id]);
    }

    // <editor-fold desc="HTMX">

    /**
     * Период премьеры для введённых в форму значений (живой предпросмотр в форме).
     *
     * @return string
     */
    public function actionPeriod(): string
    {
        $form = new ShowcaseForm();
        $form->load(Yii::$app->request->post());

        return $this->renderPeriod($form);
    }

    /**
     * Добавить спектакль в ручную витрину; возвращает весь список.
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionAddItem(): string
    {
        [$showcaseId, $performanceId] = $this->itemKey();
        $this->domain(fn() => $this->service->addItem($showcaseId, $performanceId));

        return $this->renderItems($this->repository->get($showcaseId));
    }

    /**
     * Убрать спектакль из ручной витрины или сбросить поправки витрины по правилу.
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionRemoveItem(): string
    {
        [$showcaseId, $performanceId] = $this->itemKey();
        $this->domain(fn() => $this->service->removeItem($showcaseId, $performanceId));

        return $this->renderItems($this->repository->get($showcaseId));
    }

    /**
     * Выбрать фото спектакля для витрины (`image_id` пуст — главное).
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionChooseImage(): string
    {
        [$showcaseId, $performanceId] = $this->itemKey();
        $imageId = $this->optionalInt('image_id');
        $this->domain(fn() => $this->service->chooseImage($showcaseId, $performanceId, $imageId));

        return $this->renderItems($this->repository->get($showcaseId));
    }

    /**
     * Показать или скрыть спектакль в витрине.
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionToggleItem(): string
    {
        [$showcaseId, $performanceId] = $this->itemKey();
        $this->domain(fn() => $this->service->toggleItem($showcaseId, $performanceId));

        return $this->renderItems($this->repository->get($showcaseId));
    }

    /**
     * Задать ручной порядок спектакля (`sort` пуст — снять).
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionMoveItem(): string
    {
        [$showcaseId, $performanceId] = $this->itemKey();
        $sort = $this->optionalInt('sort');
        $this->domain(fn() => $this->service->moveItem($showcaseId, $performanceId, $sort));

        return $this->renderItems($this->repository->get($showcaseId));
    }

    // </editor-fold>

    /**
     * Сегодняшний день в часовом поясе приложения.
     *
     * @return DateTimeImmutable
     */
    private function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone(Yii::$app->timeZone));
    }

    /**
     * Рассчитанный период для значений формы.
     *
     * @param ShowcaseForm $form
     * @return string
     */
    private function renderPeriod(ShowcaseForm $form): string
    {
        $today = $this->today();
        $rule = $form->rule();

        return $this->renderPartial('_period', [
            'manual' => $rule->source === Source::Manual,
            'today' => $today,
            'range' => $this->periods->resolve($rule, $today),
        ]);
    }

    /**
     * Список спектаклей витрины.
     *
     * @param Showcase $showcase
     * @return string
     */
    private function renderItems(Showcase $showcase): string
    {
        $today = $this->today();
        $range = $this->periods->resolve($showcase->rule(), $today);
        $performances = $this->repository->preview($showcase, $range);
        $ids = array_map(static fn($p): int => (int)$p->id, $performances);

        return $this->renderPartial('_items', [
            'showcase' => $showcase,
            'today' => $today,
            'range' => $range,
            'performances' => $performances,
            'items' => $this->repository->itemsByPerformance((int)$showcase->id),
            'publicIds' => $this->repository->publicIds($ids),
            'candidates' => $showcase->isManual() ? $this->repository->candidates((int)$showcase->id) : [],
        ]);
    }

    /**
     * Пара «витрина + спектакль» из POST.
     *
     * @return array{0: int, 1: int}
     * @throws BadRequestHttpException
     */
    private function itemKey(): array
    {
        $showcaseId = $this->optionalInt('showcase_id');
        $performanceId = $this->optionalInt('performance_id');
        if ($showcaseId === null || $performanceId === null) {
            throw new BadRequestHttpException('Не указаны витрина или спектакль.');
        }
        return [$showcaseId, $performanceId];
    }

    /**
     * @param string $name
     * @return int|null
     */
    private function optionalInt(string $name): ?int
    {
        $value = Yii::$app->request->post($name);
        return ($value === null || $value === '') ? null : (int)$value;
    }

    /**
     * Ошибки предметной области — в 400 с текстом; остальное всплывает к ErrorHandler.
     *
     * @param callable $operation
     * @return void
     * @throws BadRequestHttpException
     */
    private function domain(callable $operation): void
    {
        try {
            $operation();
        } catch (DomainException $e) {
            throw new BadRequestHttpException($e->getMessage(), 0, $e);
        }
    }
}
