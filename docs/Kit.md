

# Обработчик комплекта одинаковых моделей

Задача: сохранить пришедшие из POST данные, где содержатся данные нескольких одинаковых форм/моделей.

Класс заполняет собственный комплект моделей `data` моделями созданными на основе данных пришедших из формы с элементами `<input>` имеющими `name` вида:
 - Mode [ attr ] []
 - Mode [ new ] [ attr ] []
 - Mode [ int $id ] [ attr ]  

Если в данных имеется `id` клас считает это моделью из БД, тогда он загрузит её, обновит ей данные и поместит эту обновлённую модель в массив.


В примерах используются класс `Order` & `OrderItem` :
```php
class Order extends \yii\base\Model {}

class OrderItem extends \yii\base\Model {

    public const STATUS_NEW = 1;
    
    public int $status;
    public int $order_id;
    public int $cost;
    public string $name;

    /**
    * @param array $params
    * @return void
    */
    public function updateAndSave( array $params )
    {
        $this->setAttributes($params)
        $this->save();
    }
}
```
___

## Обработка хардкодных элементов

К примеру имеется список хардкодных элементов  
`view`
```HTML
 <input type="text" name="OrderItem[id][]">
 <input type="text" name="OrderItem[cost][]">
 <input type="text" name="OrderItem[id][]">
 <input type="text" name="OrderItem[cost][]">
 <input type="text" name="OrderItem[id][]">
 <input type="text" name="OrderItem[cost][]">
```

Код обработчика:
```php
// добавит в коллекцию модели из запроса
$kit = new Kit( OrderItem::class, Yii::$app->request->post() ); 
// сохранение всех моделей
$kit->save();
```
Краткая запись, без создания переменной:
```php 
(new Kit( OrderItem::class, Yii::$app->request->post() ))->save();
```
Если надо перебрать все модели и изменить/обновить какие-то данные.  
Показан способ загрузить данные в коллекцию через метод: `loadModels()` и используется транзакция
```php
public function actionCreate()
{
    $order = new Order();
    $orderItem = new OrderItem();
    
    if ( Yii::$app->request->isPost )
    {
        $post = Yii::$app->request->post();
        
        // транзакция
        $transaction = Yii::$app->db->beginTransaction();
        
        try 
        {   
            // Сперва создаём запись в БД требуемую для связи 
            if ( $order->load( $post ) AND $order->save( $post ) )
            {
                // Инициализация коллекции, указываем с какой моделью она будет работать и 
                $kit = new Kit( OrderItem::class );
                
                // Загружаем данные
                $kit->loadModels($params);
                 
                // Перебираем все модели обновляя данные 
                $kit->foreach(function( OrderItem $model ) use($order) {
                   $model->order_id = $order->id;
                });
                 
                // Сохраняем все модели
                $kit->save();
            }
            
            $transaction->commit();
            
            return $this->redirect( 'url' ); // or some action
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
    
    return $this->render('template', [
        'order'     => $order,
        'orderItem' => $orderItem,
    ]);
}

```
___

## Генерация полей
Коллекция может задать нужные имена для полей заполнения, используя метод `field()`.  
Можно использовать обработчик моделей который предварительно сам загрузит все модели `handler()`
`view`
```php
$form = ActiveRecord::begin();
$orderItemKit->setForm($form);

$form->field($order, 'number')->textInput();

foreach( $orderItemKit->getData() as $orderItem )
{
    $orderItemKit
        ->field( $orderItem, 'name',  'textInput', ['maxlength' => true] )
        ->label('товар');
        
    $orderItemKit
        ->field( $orderItem, 'cost',  'textInput', ['maxlength' => true] )
        ->label('кол-во');
}
```
`Action`
```php
public function actionUpdate( int $id )
{
    $order = new Order();
    $orderItemKit = new Kit( OrderItem::class );
    $orderItemKit->addModels(
        OrderItems::find()
            ->where(['order_id' => $id])
            ->all()
    );
    $orderItemKit->addModel( new OrderItems() );
    
    if ( Yii::$app->request->isPost ) {
        $post = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            if ( $order->load( $post ) AND $order->save( $post ) ) {
                $kit->postHandler(function( OrderItem $model ) use($order) {
                   $model->order_id = $order->id;
                   $model->save();
                });
            }
            
            $transaction->commit();
            
            return $this->redirect( 'url' ); // or some action
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    return $this->render('template', [
        'order'                 => $order,
        'orderItemModels'       => $orderItemModels,
        'orderItemCollection'   => $orderItemKit,
    ]);
}
```
___

# Методы.
Публичные.



## Создание модели и добавление модели в коллекцию

### public function loadModels()
- array $params  
- bool $prepare = false  
- return: self  

Пакетное заполнение коллекции(массив `data`) моделями.  

<small>При наличии `html` элементов у которых значение аттрибута `name` имеет вид как описано в оглавлении</small>
```php
$kit = new Kit( Post::class);
$kit->loadModels([
    'name` => [
        '...1',
        '...2',
    ],
    'description' => [
        '..description..1' ,
        '..description..2'
    ]
]);
```
<small>При получении подготовленного массива надо передавать `true` в параметре `prepare`</small>
```php
$kit = new Kit( Post::class);
$kit->loadModels([
   [
       'name' => '...1',
       'description' => '..description..1'
   ],
   [
       'name' => '...2',
       'description' => '..description..2'
   ],
], ( $prepare = true ));
```
___

### public function addModel()
- ActiveRecord $model  
- bool $save = false  
- bool $validation = true  
- return: ActiveRecord  

Добавление готовой модели в коллекцию(массив `data`) .  
В массив `data` добавляется `$model` (экземпляр класса  `ActiveRecord`)

Отличие:
- `insertModel` добавляет в коллекцию модель из базы данных
- `constructModel` получая параметры добавляет модель в коллекцию
- `createInstance` получая параметры не добавляет модель в коллекцию

```php
$kit = new Kit( OrderItem::class );
 
$model = new OrderItem();
$model->name = '...';
$model->cost = 1
 
$kit->addModel( $model );
```
___

### public function addModelList()
 - array $model  
 - bool $save = false  
 - bool $validation = true  
 - return: self  
  
Добавление в коллекцию(массив `data`) моделей из массива переданного первым аргументом.  
В массив `data` добавляются множество `$model` (экземпляр класса  `ActiveRecord`)

```php
$orderItemKit = new Kit( OrderItem::class );

$orderItemKit->addModelList(
    OrderItems::find()
        ->where(['order_id' => $order->id])
        ->all() 
);
```
___

### public function insertModel()
- int $id  
- array $params  
- bool $save = false  
- bool $save = false  
- bool $validation = true  
- return: ActiveRecord  

Добавление в коллекцию (массив `data`) модели из БД с обновлёнными данными (без сохранения после обновления).

Отличие:
- `addModel` добавляет в коллекцию объект ActiveRecord
- `constructModel` получая параметры добавляет модель в коллекцию
- `createInstance` получая параметры не добавляет модель в коллекцию

```php
$orderItemKit = new Kit( OrderItem::class );
     
$orderItemParamsList = $kit->getParams();

foreach ( $orderItemParamsList as $orderItemParams ) {
   $orderItemKit->insertModel( $orderItemParams['id'], $orderItemParams );
}

$orderItemKit->save();
```
___

### public function constructModel( ): ActiveRecord
- array $params  
- bool $save = false  
- bool $validation = true  
- return: ActiveRecord  

Добавление новой модели в коллекцию(массив `data`).  
В коллекцию(массив `data`) добавляется экземпляр класса `ActiveRecord` с данными из массива `$params`
Опционально:
- можно сохранить модель
- можно отменить валидацию при сохранении

Отличие:
- `addModel` добавляет в коллекцию объект ActiveRecord
- `insertModel` добавляет в коллекцию модель из базы данных
- `createInstance` получая параметры не добавляет модель в коллекцию

```php
$orderItemKit = new Kit( Post::class );

$orderItemKit
    ->constructModel([
       'name' => '...',
       'cost' => 1,
    ])
    ->save();
```
___

### public function createInstance()
- array $params = []  
- bool $save = false  
- bool $validation = true  
- return: ActiveRecord  
  
Возвращает новую модель.  
  
Опционально:
- можно задать данные модели
- можно сохранить модель
- можно отменить валидацию при сохранении

Отличие:
- `addModel` добавляет в коллекцию объект ActiveRecord
- `insertModel` добавляет в коллекцию модель из базы данных
- `constructModel` получая параметры добавляет модель в коллекцию

```php
$orderItemKit = new Kit( Post::class );

$model = $orderItemKit->createInstance();

$model->name = '...';
$model->cost = 1;
$model->save();
```

Задать данные и сразу сохранить модель
```php
$orderItemKit = new Kit( Post::class );

$model = $orderItemKit->createInstance([
    'name' => '...',
    'cost' => 1,
], ( $save = true ));
```


___

## Обработчики

### public function foreach()
- callable $callback
- return: self

Перебор в цикле всех моделей коллекции и вызов функции из аргумента с передачей в callback функцию модели в качестве первого аргумента
```php
$orderItemKit = new Kit( OrderItem::class );

$orderItemKit->handler();

$orderItemKit->foreach(function($model) {
    $model->status = Status::STATUS_NEW;
    $model->save();
})
```
___

### public function postHandler()  
- ?callable $callback = null
- return: self

Создание моделей из данных в POST запросе.  

Опционально:
- принимает в качестве первого аргумента callback функцию в которую будет передана модель

```php
$orderItemKit = new Kit( OrderItem::class );
$orderItemKit->handler();
$orderItemKit->save();
```
Применение цепочки вызовов.
```php
$orderItemKit = new Kit( OrderItem::class );
$orderItemKit->handler()->save();
```
использование callback функции:
```php
$orderItemKit = new Kit( OrderItem::class );

$orderItemKit->handler( function( OrderItem $model ){
    $model->status = OrderItem::STATUS_NEW;
    $model->save();
});
```
___

### public function save()
- return: self  
Сохранение всех моделей в коллекции (Вызов у всех моделей метода `save()` )  

Опционально:
- можно отменить валидацию при сохранении

```php
$orderItemKit = new Kit( OrderItem::class );
$orderItemKit->handler();
$orderItemKit->save();
```


___

## Взаимодействие с формой

### public function setFormConfig()
- ActiveForm $form  
- return: self  

Установки для конструктора форм

```php
$kit = new Kit( OrderItem::class );
$kit->setFormConfig($form, $model);
```
___

### public function field()
- ActiveRecord $model  
- string $attr  
- string $method  
- array $params = []  
- return: ActiveField  
  
Конструктор полей который задаёт имя поля вида: `Model[{id}][attr]` / `Model['new'][attr][]`

```php
$form = ActiveRecord::begin();
$orderItemKit = new Kit( OrderItem::class );
$orderItemKit->addModelList( OrderItems::find()->where(['order_id' => $order->id])->all() )
$orderItemKit->addModel( new OrderItems() );
$orderItemKit->addModel(new OrderItems() );

$orderItemKit->setForm($form);

foreach( $orderItemKit->getData() as $orderItem )
{
    $orderItemKit->field( $orderItem, 'name',  'textInput', ['maxlength' => true] )->label('товар');
    $orderItemKit->field( $orderItem, 'cost',  'textInput', ['maxlength' => true] )->label('товар');
}
```


___

## Геттеры

### public function getData()  
 - return: ActiveRecord[]  
  
Получение коллекции моделей для внешнего использования

```php
$kit = new Kit( Post::class, Yii::$app->request->post() );
     
foreach( $kit->getData() as $model ) {
   $model->save();
}
```
___

### public function getPostData()  
- return: ?array  

Метод возвращает из `post` массив данных из ключа аналогичного имени класса `$this->className`

```php
$kit = new Kit( Post::class );
 
$data = $kit->getPostData();
```


___

## Дополнительный функционал

### public function drop()  
Удаление всех моделей из коллекции

```php
$orderItemKit = new Kit( OrderItem::class );
$orderItemKit->drop();
```
