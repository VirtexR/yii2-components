

# Помощник загрузки файлов.

Решаемая задача: задать путь загрузки файла, загрузить файл, дать атрибуту модели имя загруженного файла. 



`upload()`  
Загрузка одного файла.

```php
use andy87\yii_components\file_uploader\Uploader;

/**
 * Example upload
 */
public function actionCreate()
{
    $model = new Order();
      
    if ( Yii::$app->request->isPost )
    {
        if ( $model->load( $this->request->post() ) && $model->save() )
        {
            $fileUploader = new Uploader( $model, ['model_property' => 'model_attr_key'], '/path/to/upload' );
        
            if ( $fileUploader->upload() ) {
                return $this->redirect([ 'view', 'id' => $model->id ]);
            }
        }
    }
}

```

`uploadMultiply()`  
Загрузка нескольких файлов

```php
use andy87\yii_components\file_uploader\Uploader;
/**
 * Example uploadMultiply
 */
public function actionUpdate( int $id )
{
    $model = Order::findOne($id);
      
    if ( Yii::$app->request->isPost )
    {
        if ( $model->load( $this->request->post() ) && $model->save() )
        {
            $fileUploader = new Uploader( $form, ['model_property' => 'model_attr_key'], '/path/to/upload' );

            if ( $fileUploader->uploadMultiply() ) {
                return $this->redirect([ 'view', 'id' => $model->id ]);
            }
        }
    }
}
```
