
# Компоненты

### Загрузка файла

Подробное описание возможностей в [README](docs/Uploader.md)

##### Пример кода.
`Action`
```php
if ( $model>load( $this->request->post() ) && $model->save() )
{
    $fileUploader = new Uploader( $model, ['model_property' => 'model_attr_key'], 'path/upload/dir' );

    if ( $fileUploader->upload() ) {
        return $this->redirect([ 'view', 'id' => $model->id ]);
    }
}
```

___

### Обработчик одинаковых моделей

Подробное описание возможностей в [README](docs/Kit.md)

##### Пример кода.

`Action`
```php
// создание коллекции для того что бы с ней работать
$orderItemKit = new Kit( OrderItem::class );

if ( Yii::$app->request->isPost )
{
    // other handler...
    
    // коллекция заполняет массив моделями с данными из post запроса и вызывает `callBack` функцию
    $orderItemKit->postHandler(function( OrderItem $model ){
        if ( $model->isNewRecord )
        {
            $model->status = OrderItem::STATUS_NEW;
        }
        $model->save();
    });
    //если надо просто сохранить все модели без модификаций:
    $orderItemKit->postHandler()->save(); 
}

// список моделей для отображения в шаблоне + 2 новые модели
$orderItemKit->addModels(
    OrderItems::find()
        ->where(['order_id' => 1])
        ->all()
);
$orderItemKit->addModel( new OrderItems() );
$orderItemKit->addModel( new OrderItems() );

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
- php ( >= 8.0 )

## composer.json
Установка с помощью [composer](https://getcomposer.org/download/)

Добавить в `composer.json`  
<small>require</small>
```
"require": {
    ...
    "andy87/yii2-components" : "1.0.1"
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
            "version"               : "1.0.1",
            "source"                : {
                "type"                  : "git",
                "reference"             : "main",
                "url"                   : "https://github.com/andy87/yii2-components"
            },
            "autoload": {
                "psr-4": {
                    "andy87\\yii_components\\" : "src"
                }
            }
        }
    }
]
```  
  
  

#### Через терминал:  

Локально:  
$ `php composer.phar require andy87/yii2-components "main"`  
Глобально:  
$ `composer require andy87/yii2-components "main"`  