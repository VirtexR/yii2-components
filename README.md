
# Компоненты

### Загрузка файла

Подробное описание возможностей в [README](src/file_uploader/README.md)

##### Пример кода.
`Action`
```php
if ( $model>load( $this->request->post() ) && $model->save() )
{
    $fileUploader = new FileUploader( $model, ['model_property' => 'model_attr_key'], 'path/upload/dir' );

    if ( $fileUploader->upload() ) {
        return $this->redirect([ 'view', 'id' => $model->id ]);
    }
}
```

___

### Обработчик одинаковых моделей

Подробное описание возможностей в [README](src/collection/README.md)

##### Пример кода.

`Action`
```php
// создание коллекции для того что бы с ней работать
$orderItemCollection = new Collection( OrderItem::class );

if ( Yii::$app->request->isPost )
{
    // other handler...
    
    // коллекция заполняет массив моделями с данными из post запроса и вызывает `callBack` функцию
    $orderItemCollection->postHandler(function( OrderItem $model ){
        if ( $model->isNewRecord )
        {
            $model->status = OrderItem::STATUS_NEW;
        }
        $model->save();
    });
    //если надо просто сохранить все модели без модификаций:
    $orderItemCollection->postHandler()->save(); 
}

// список моделей для отображения в шаблоне + 2 новые модели
$orderItemCollection->addModels(
    OrderItems::find()
        ->where(['order_id' => 1])
        ->all()
);
$orderItemCollection->addModel( new OrderItems() );
$orderItemCollection->addModel( new OrderItems() );

```

`view`
```php

$form = ActiveRecord::begin();

// указываем коллекции (пришедшей из `action`) с какой формой она работает
$orderItemCollection->setForm($form);

// Other input fields 

// выводим поля для заполнения, где аттрибут `name` будет подходить для обработки классом `Collection` 
foreach( $orderItemCollection->getData() as $orderItem )
{
    $orderItemCollection
        ->field( $orderItem, 'name',  'textInput', ['maxlength' => true] ) //возвращает ActiveField
        ->label('товар');
        
    $orderItemCollection
        ->field( $orderItem, 'cost',  'textInput', ['maxlength' => true] )
        ->label('кол-во');
}
```
  
___
# Установка

## Зависимости
- php ( >= 7.4 )
- ext-curl
- ext-json
- ext-mbstring

## composer.json
Установка с помощью [composer](https://getcomposer.org/download/)

Локально:
$ `php composer.phar require andy87/yii2-components "master"`

Глобально:
$ `composer require andy87/yii2-components "master"`

Добавить в `composer.json`  
<small>require</small>
```
"require": {
    ...
    "andy87/yii2-components" : "1.0.0"
},
```
<small>repositories</small>
```
"repositories": [
    ...,
    {
        "type"                  : "package",
        "package"               : {
            "name"                  : "andy87/yii2-components",
            "version"               : "1.0.0",
            "source"                : {
                "type"                  : "git",
                "reference"             : "main",
                "url"                   : "https://github.com/andy87/yii2-components"
            },
            "autoload": {
                "psr-4": {
                    "andy87\\yii_components\\" : "src",
                }
            }
        }
    }
]
```
