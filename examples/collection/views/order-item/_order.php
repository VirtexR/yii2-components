<?php

use yii\web\View;
use yii\widgets\ActiveForm;
use app\models\OrderItem;
use andy87\yii_components\collection\Collection;

/** @var View $this  */
/** @var Collection $orderItemsCollection  */
/** @var ActiveForm $form  */


/**
 * @var int $index
 * @var OrderItem $orderItem
 */
foreach ( $orderItemsCollection->getData() as $index => $orderItem )
{
    $index++;

    echo $orderItemsCollection->field( $orderItem, 'cost', 'textInput', [
            'placeholder' => 'кол-во товара'
        ])
        ->label("Кол-во товара $index");

    echo $orderItemsCollection->field( $orderItem, 'price', 'textInput', [
            'placeholder' => 'стоимость товара'
        ])
        ->label("Кол-во товара $index");


    // без дополнительных параметров
    echo $orderItemsCollection->field( $orderItem, 'article', 'hiddenInput');

    ?>
    <!-- если надо применить кастомный HTML -->
    <div class="row">
        <label>
            <input
                type="number"
                name="<?=$orderItemsCollection->generateName($orderItem, 'article')?>"
                value=""
            >
        </label>
    </div>
<?php
}