<?php

use yii\web\View;
use yii\widgets\ActiveForm;
use app\models\OrderItem;
use andy87\yii_components\Kit;

/** @var View $this  */
/** @var Kit $orderItemsKit  */
/** @var ActiveForm $form  */


/**
 * @var int $index
 * @var OrderItem $orderItem
 */
foreach ( $orderItemsKit->getData() as $index => $orderItem )
{
    $index++;

    echo $orderItemsKit->field( $orderItem, 'cost', 'textInput', [
            'placeholder' => 'кол-во товара'
        ])
        ->label("Кол-во товара $index");

    echo $orderItemsKit->field( $orderItem, 'price', 'textInput', [
            'placeholder' => 'стоимость товара'
        ])
        ->label("Кол-во товара $index");


    // без дополнительных параметров
    echo $orderItemsKit->field( $orderItem, 'article', 'hiddenInput');

    ?>
    <!-- если надо применить кастомный HTML -->
    <div class="row">
        <label>
            <input
                type="number"
                name="<?=$orderItemsKit->generateName($orderItem, 'article')?>"
                value=""
            >
        </label>
    </div>
<?php
}