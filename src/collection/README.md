

# Обработчик одинаковых моделей

Задача: сохранить пришедшие из POST данные, где содержатся данные нескольких одинаковых форм.

Класс умеет заполнять собственный массив `data` моделями созданными на основе данных пришедших из формы с элементами `<input>` имеющими `name` вида:
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
$collection = new Collection( OrderItem::class, Yii::$app->request->post() ); 
// сохранение всех моделей
$collection->save();
```
Краткая запись, без создания переменной:
```php 
(new Collection( OrderItem::class, Yii::$app->request->post() ))->save();
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
                $collection = new Collection( OrderItem::class );
                
                // Загружаем данные
                $collection->loadModels($params);
                 
                // Перебираем все модели обновляя данные 
                $collection->foreach(function( OrderItem $model ) use($order) {
                   $model->order_id = $order->id;
                });
                 
                // Сохраняем все модели
                $collection->save();
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
$orderItemCollection->setForm($form);

$form->field($order, 'number')->textInput();

foreach( $orderItemCollection->getData() as $orderItem )
{
    $orderItemCollection
        ->field( $orderItem, 'name',  'textInput', ['maxlength' => true] )
        ->label('товар');
        
    $orderItemCollection
        ->field( $orderItem, 'cost',  'textInput', ['maxlength' => true] )
        ->label('кол-во');
}
```
`Action`
```php
public function actionUpdate( int $id )
{
    $order = new Order();
    $orderItemCollection = new Collection( OrderItem::class );
    $orderItemCollection->addModels(
        OrderItems::find()
            ->where(['order_id' => $id])
            ->all()
    );
    $orderItemCollection->addModel( new OrderItems() );
    
    if ( Yii::$app->request->isPost ) {
        $post = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            if ( $order->load( $post ) AND $order->save( $post ) ) {
                $collection->postHandler(function( OrderItem $model ) use($order) {
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
        'orderItemCollection'   => $orderItemCollection,
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
$collection = new Collection( Post::class);
$collection->loadModels([
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
$collection = new Collection( Post::class);
$collection->loadModels([
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
$collection = new Collection( OrderItem::class );
 
$model = new OrderItem();
$model->name = '...';
$model->cost = 1
 
$collection->addModel( $model );
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
$orderItemCollection = new Collection( OrderItem::class );

$orderItemCollection->addModelList(
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
$orderItemCollection = new Collection( OrderItem::class );
     
$orderItemParamsList = $collection->getParams();

foreach ( $orderItemParamsList as $orderItemParams ) {
   $orderItemCollection->insertModel( $orderItemParams['id'], $orderItemParams );
}

$orderItemCollection->save();
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
$orderItemCollection = new Collection( Post::class );

$orderItemCollection
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
$orderItemCollection = new Collection( Post::class );

$model = $orderItemCollection->createInstance();

$model->name = '...';
$model->cost = 1;
$model->save();
```

Задать данные и сразу сохранить модель
```php
$orderItemCollection = new Collection( Post::class );

$model = $orderItemCollection->createInstance([
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
$orderItemCollection = new Collection( OrderItem::class );

$orderItemCollection->handler();

$orderItemCollection->foreach(function($model) {
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
$orderItemCollection = new Collection( OrderItem::class );
$orderItemCollection->handler();
$orderItemCollection->save();
```
Применение цепочки вызовов.
```php
$orderItemCollection = new Collection( OrderItem::class );
$orderItemCollection->handler()->save();
```
использование callback функции:
```php
$orderItemCollection = new Collection( OrderItem::class );

$orderItemCollection->handler( function( OrderItem $model ){
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
$orderItemCollection = new Collection( OrderItem::class );
$orderItemCollection->handler();
$orderItemCollection->save();
```


___

## Взаимодействие с формой

### public function setFormConfig()
- ActiveForm $form  
- return: self  

Установки для конструктора форм

```php
$collection = new Collection( OrderItem::class );
$collection->setFormConfig($form, $model);
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
$orderItemCollection = new Collection( OrderItem::class );
$orderItemCollection->addModelList( OrderItems::find()->where(['order_id' => $order->id])->all() )
$orderItemCollection->addModel( new OrderItems() );
$orderItemCollection->addModel(new OrderItems() );

$orderItemCollection->setForm($form);

foreach( $orderItemCollection->getData() as $orderItem )
{
    $orderItemCollection->field( $orderItem, 'name',  'textInput', ['maxlength' => true] )->label('товар');
    $orderItemCollection->field( $orderItem, 'cost',  'textInput', ['maxlength' => true] )->label('товар');
}
```


___

## Геттеры

### public function getData()  
 - return: ActiveRecord[]  
  
Получение коллекции моделей для внешнего использования

```php
$collection = new Collection( Post::class, Yii::$app->request->post() );
     
foreach( $collection->getData() as $model ) {
   $model->save();
}
```
___

### public function getPostData()  
- return: ?array  

Метод возвращает из `post` массив данных из ключа аналогичного имени класса `$this->className`

```php
$collection = new Collection( Post::class );
 
$data = $collection->getPostData();
```


___

## Дополнительный функционал

### public function drop()  
Удаление всех моделей из коллекции

```php
$orderItemCollection = new Collection( OrderItem::class );
$orderItemCollection->drop();
```
