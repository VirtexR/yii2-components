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

        //всё стандартно...
        if ( Yii::$app->request->isPost )
        {
            // `load()` внутри переназначен
            if ( $orderForm->load( Yii::$app->request->post()) )
            {
                // `save()` внутри переназначен
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
        // `findOne()` внутри переназначен
        if ( $orderForm = OrderForm::findOne($id))
        {
            return $orderForm;
        }

        return null;
    }

}