<?php

use app\models\forms\OrderForm;
use yii\web\View;
use yii\widgets\ActiveForm;

/** @var View $this  */
/** @var OrderForm $orderForm  */

$form = ActiveForm::begin();


$form->field($orderForm, 'user_id')->hiddenInput(['value' => Yii::$app->user->identity->getId() ]);

echo $this->render('@views/order-item/_order',[
    'orderItemsCollection' => $orderForm->orderItemsCollection,
    'form' => $form
]);

