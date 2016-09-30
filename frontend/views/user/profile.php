<?php
/* @var $this yii\web\View */
/* @var array $geeks common\models\Geeks */
/* @var $user common\models\User */

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Subscription;
use Yii;

?>
<div class="col-sm-4">
    <div class="panel panel-default floating">
        <header class="main-box-header clearfix">
            <h2><?= $user->username ?></h2>
        </header>

        <?php if ($user->isUserAdmin($user->username)): ?>
        <div class="profile-label">
            <span class="label label-danger">Admin</span>
        </div>
        <?php endif; ?>

        <div class="panel-body">
            <?php if (Subscription::isRelationExist(Yii::$app->user->id, $user->id)): ?>
                <a href="<?= Url::to(['user/unsubscribe', 'id' => $user->id]); ?>"><button type="button" id="subscribe_button" class="btn btn-success btn-lg">Подписан</button></a>
            <?php else: ?>
                <a href="<?= Url::to(['user/subscribe', 'id' => $user->id]); ?>"><button type="button" class="btn btn-info btn-lg">Подписаться</button></a>
            <?php endif; ?>
        </div>

    </div>
</div>
<div class="col-sm-8 blog-main">
    <div class="row">
        <div class="col-sm-12">

            <?php foreach ($geeks as $geek): ?>
                <section class="blog-post">
                    <div class="panel panel-default">
                        <?php if ($geek->thumbnail): ?>
                            <?= Html::img(Yii::$app->urlManagerBackend->createUrl($geek->image), ['class' => "img-responsive"]) ?>
                        <?php endif; ?>
                        <div class="panel-body">
                            <div class="blog-post-meta">
                                <a href="<?= Url::to(['user/profile', 'id' => $geek->user_id]) ?>"><span class="label label-light label-primary"><?= $geek->getAuthor()->username ?></span></a>
                                <p class="blog-post-date pull-right"><?= $geek->updated_at ?></p>
                            </div>
                            <div class="blog-post-content">
                                <a href="<?= Url::to(['geeks/view', 'id' => $geek->id]); ?>">
                                    <h2 class="blog-post-title"><?= Html::encode($geek->text) ?></h2>
                                </a>
                                <p></p>
                                <button type="button" class="btn btn-primary"><i class="fa fa-heart"></i></button>
                                <a class="blog-post-share pull-right" href="#">
                                    <i class="material-icons">&#xE80D;</i>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>

        </div>
    </div>
</div>