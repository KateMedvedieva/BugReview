<?php

namespace app\controllers;

use app\models\Bugs;
use app\models\Filter;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\httpclient\Client;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new Filter();
        $filterDates = Yii::$app->request->get('Filter', []); // Додаємо дефолтне значення як пустий масив
        $startDate = isset($filterDates['startDate']) ? $filterDates['startDate'] : null;
        $endDate = isset($filterDates['endDate']) ? $filterDates['endDate'] : null;

        $query = Bugs::find();

        if ($startDate) {
            $query->andWhere(['>=', 'createdDate', $startDate]);
            $model->startDate = $startDate;
        }
        if ($endDate) {
            $query->andWhere(['<=', 'createdDate', $endDate]);
            $model->endDate = $endDate;
        }

        $data = $query->all();

        $dataArray = array_map(function ($model) {
            $attrs = $model->attributes;
            unset($attrs['title']);
            unset($attrs['rootCause']);
            $attrs['Platform'] = $model->platform->name;
            return $attrs;
        }, $data);

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('http://172.19.0.2:5000/process')
            ->addHeaders(['content-type' => 'application/json'])
            ->setContent(json_encode($dataArray))
            ->send();

        if ($response->isOk) {
            $result = $response->data;
            return $this->render('index', [
                'data' => $result,
                'model' => $model
            ]);
        }

        return $this->render('index', [
            'data' => [],
            'model' => $model
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
