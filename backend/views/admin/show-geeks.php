<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php foreach ($geeks as $geek): ?>

    <div class="main-box clearfix">
        <header class="main-box-header clearfix">
            <h2><?= $geek->text ?></h2>
        </header>
        <div class="main-box-body clearfix">
            <a href="<?= Url::to(['admin/edit-geek', 'id' => $geek->id]); ?>" >
                <i class="fa fa-edit"></i>
                Изменить
            </a>
            <a href="<?= Url::to(['admin/delete-geek', 'id' => $geek->id]); ?>">
                <i class="fa fa-trash-o"></i>
                Удалить
            </a>
        </div>
    </div>

<?php endforeach; ?>