<?php

namespace app\controllers;

use app\models\forms\OrderForm;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 *  Class OrderController - Заказы.
 *
 */
class OrderController extends Controller
{
    /**
     * @param int $id
     * @return string|Response
     */
    public function actionUpdate( int $id ): string|Response
    {
        $orderForm = $this->find($id);

        if ( Yii::$app->request->isPost )
        {
            if ( $orderForm->load( Yii::$app->request->post()) )
            {
                if ( $orderForm->save() )
                {
                    return $this->redirect("/order/view/{$orderForm->id}");
                }
            }
        }

        return $this->render('update', [
            'orderForm' => $orderForm
        ]);
    }

    /**
     * @param int $id
     * @return OrderForm|null
     */
    private function find( int $id ): ?OrderForm
    {
        if ( $orderForm = OrderForm::findOne($id))
        {
            return $orderForm;
        }

        return null;
    }

}