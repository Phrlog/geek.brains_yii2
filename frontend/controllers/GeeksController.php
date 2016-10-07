<?php
namespace frontend\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;
use common\models\Geeks;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\GeekForm;
use yii\web\UploadedFile;
use common\models\User;
use common\models\Likes;
use yii\web\Response;
use common\models\Subscription;

/**
 * Geeks controller
 */
class GeeksController extends Controller
{


    public $layout = 'base';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'geeks', 'create', 'view', 'feed', 'like'],
                'rules' => [
                    [
                        'actions' => ['create', 'index', 'geeks', 'view', 'feed', 'like'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', 'geeks', 'view'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'like') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect('geeks/all');
    }

    public function actionAll()
    {
        $geeks = Geeks::find()
            ->select(['geeks.*', 'user.username', 'COUNT(likes.geek_id) as count'])
            ->join('INNER JOIN', User::tableName(),'user.id = geeks.user_id')
            ->join('LEFT JOIN', Likes::tableName(), 'likes.geek_id = geeks.id')
            ->groupBy(['geeks.id'])
            ->orderBy(['geeks.created_at' => SORT_DESC])
            ->all();

        if (Yii::$app->user->id){
            $query = new Query();
            $query = $query->select(['geek_id'])->from(Likes::tableName())->where(['user_id' => Yii::$app->user->id])->all();
            for ($i = 0; $i< count($query); $i++) {
                $likes[] = $query[$i]['geek_id'];
            }
        } else {
            $likes = [];
        }

        return $this->render('all',[
            'geeks' => $geeks,
            'likes' => $likes
        ]);
    }

    public function actionView($id)
    {
        $geek = Geeks::find()
            ->select(['geeks.*', 'COUNT(likes.geek_id) as count'])
            ->join('LEFT JOIN', Likes::tableName(), 'likes.geek_id = geeks.id')
            ->where(['id' => $id])
            ->groupBy(['geeks.id'])
            ->one();

        if ($geek === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('view', [
            'geek' => $geek,
        ]);
    }

    public function actionCreate()
    {
        $model = new GeekForm();
        $text = Yii::$app->request->post('GeekForm')['text'];

        if ($model->load(Yii::$app->request->post())) {

            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            $geek = new Geeks();

            if ($model->upload()) {
                $path = 'upload/' . Yii::$app->user->id;

                $geek->image = $path . '/original/' . $model->imageFile->baseName . '.' . $model->imageFile->extension;
                $geek->thumbnail = $path . '/thumbnail/' . $model->imageFile->baseName . '.' . $model->imageFile->extension;
            }


            $geek->user_id = Yii::$app->user->id;
            $geek->text = $text;

            if ($geek->save()) {
                $result = "Твит успешно опубликован";
                $alert_type = 'success';
            } else {
                $result = "Неудача! Попробуйте снова";
                $alert_type = 'error';
            }

            Yii::$app->session->setFlash($alert_type, $result);

            return $this->refresh();
        }

        return $this->render('create', [
            'model'  => $model,
        ]);
    }

    public function actionFeed()
    {
        // Find geeks of users on which we subscribed
        $geeks = Geeks::find()->select(['geeks.*', 'user.username', 'COUNT(likes.geek_id) as count'])
                ->join('INNER JOIN', User::tableName(),'user.id = geeks.user_id')
                ->join('INNER JOIN', Subscription::tableName(), 'subscription.subscribe_id = user.id')
                ->join('LEFT JOIN', Likes::tableName(), 'likes.geek_id = geeks.id')
                ->where(['subscription.user_id' => Yii::$app->user->id])
                ->orWhere(['geeks.user_id' => Yii::$app->user->id])
                ->groupBy(['geeks.id'])
                ->orderBy(['geeks.created_at' => SORT_DESC])
                ->all();

        // Find geeks that we liked
        $query = new Query();
        $query = $query->select(['geek_id'])->from(Likes::tableName())->where(['user_id' => Yii::$app->user->id])->all();
        for ($i = 0; $i< count($query); $i++) {
            $likes[] = $query[$i]['geek_id'];
        }

        return $this->render('all',[
            'geeks' => $geeks,
            'likes' => $likes
        ]);
    }

    public function actionLike()
    {

        if (Yii::$app->request->isAjax)
        {
            $like = new Likes();
            $geek_id = Yii::$app->request->post('id');

            if (Geeks::findOne($geek_id) === null) {
                throw new NotFoundHttpException;
            }

            if (Likes::isRelationExist(Yii::$app->user->id, $geek_id)){
                Likes::find()->where(['user_id' => Yii::$app->user->id, 'geek_id' => $geek_id])->one()->delete();
                $option = 'delete';
            } else {
                $like->user_id = Yii::$app->user->id;
                $like->geek_id = $geek_id;
                $like->save();
                $option = 'add';
            }

            Yii::$app->response->format = Response::FORMAT_JSON;

            $count = Likes::find()->where(['geek_id' => $geek_id])->count();

            return ['status' => 'success', 'option' => $option, 'count' => $count];
        }

    }

}
