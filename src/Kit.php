<?php /**
 * @author Andrey and_y87 Kidin
 * @url https://github.com/andy87/yii2-components
 */

namespace andy87\yii_components;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use yii\widgets\{ActiveForm,ActiveField};

/**
 * Класс взаимодействия с коллекциями новых моделей (для создания нескольких моделей)
 *
 * @property ActiveRecord $class Модель используемая в коллекции
 * @property string $className Имя класса без пространства имён
 * @property ActiveForm $form Для создания полей
 * @property array $data Список моделей коллекции
 * @property int $i Итератор, для подсчёта `id` новых моделей
 */
class Kit
{
    /** @var ActiveRecord Модель используемая в коллекции */
    public ActiveRecord $class;

    /** @var string Имя класса без пространства имён */
    public string $className;

    /** @var ActiveForm Для создания полей */
    public ActiveForm $form;



    /** @var array $data Список моделей коллекции */
    private array $data = [];

    /** @var int $i Итератор, для подсчёта `id` новых моделей */
    private int $i = 1;





    /**
     * Конструктор
     *
     * @param string|ActiveRecord $class Класс модели для которой собирается коллекция
     * @param array $data Массив аттрибутов и данных моделей которые будут добавлены в коллекцию
     */
    public function __construct( string|ActiveRecord $class, array $data = [] )
    {
        $this->className = StringHelper::basename(get_class($class));

        $this->class = $class;

        $this->loadModels($data);
    }



    // Создание моделей

    /**
     * Пакетное заполнение коллекции(массив `data`) моделями.
     *
     * @param array $params Массив аттрибутов и их данных
     * @return self
     */
    public function loadModels( array $params = [] ): self
    {
        $paramsList = ( empty($params) )
            ? $this->getPostData()
            : ( $params[ $this->className ] ?? $params );

        foreach ( $paramsList as $id => $attributes )
        {
            if ( is_integer($id) &&  $id > 0 )
            {
                $this->insertModel( $id, $attributes );

            } else {

                $this->constructModel( $attributes );
            }
        }

        return $this;
    }

    /**
     * Добавление готовой модели в коллекцию(массив `data`).
     * В массив `data` добавляется `$model` (экземпляр класса `ActiveRecord`)
     *
     *  опционально:
     *      - можно сохранить модели
     *      - можно отменить валидацию при сохранении
     *
     * @param ActiveRecord $model Модель, которая будет добавлена в коллекцию
     * @param bool $save Сохранить модель?
     * @param bool $validation При сохранении валидировать модель?
     * @return ActiveRecord
     */
    public function addModel( ActiveRecord $model, bool $save = false, bool $validation = true ): ActiveRecord
    {
        if ( $save ) {
            $model->save( $validation );
        }

        $this->data[] = $model;

        return $model;
    }

    /**
     * Добавление в коллекцию модели из массива переданного первым аргументом
     *  В массив `data` добавляются множество `$model` (экземпляр класса  `ActiveRecord`)
     *
     *  опционально:
     *      - можно сохранить модели
     *      - можно отменить валидацию при сохранении
     *
     * @param array $models Массив с моделями ActiveRecord
     * @param bool $save Сохранить модель?
     * @param bool $validation При сохранении валидировать модель?
     * @return self
     */
    public function addModelList( array $models, bool $save = false, bool $validation = true  ): self
    {
        foreach( $models as $model ) {
            $this->addModel( $model, $save, $validation );
        }

        return $this;
    }

    /**
     * Добавление в массив `data` модели из БД с обновлёнными данными
     *
     *  опционально:
     *      - можно сохранить модели
     *      - можно отменить валидацию при сохранении
     *
     * @param int $id ID модели
     * @param array $params Новые параметры модели
     * @param bool $save Сохранить модель?
     * @param bool $validation При сохранении валидировать модель?
     * @return ActiveRecord
     */
    public function insertModel( int $id, array $params, bool $save = false, bool $validation = true ): ActiveRecord
    {
        $model = $this->class::findOne($id);

        $model->setAttributes($params);

        $this->addModel( $model, $save, $validation );

        return $model;
    }

    /**
     * @param $criteria
     * @return self
     */
    public function findModels($criteria): self
    {
        $this->data = $this->class::findAll($criteria);

        return $this;
    }

    /**
     * Добавление новой модели в коллекцию
     *  в массив `data` добавляется экземпляр класса `ActiveRecord` с данными из массива `$params`
     *
     *  опционально:
     *      - можно сохранить модели
     *      - можно отменить валидацию при сохранении
     *
     * @param array $params Массив аттрибутов и их данных
     * @param bool $save Сохранить модель?
     * @param bool $validation При сохранении валидировать модель?
     * @return ActiveRecord
     */
    public function constructModel( array $params, bool $save = false, bool $validation = true ): ActiveRecord
    {
        $model = $this->createInstance( $params, $save, $validation );

        $this->addModel( $model );

        return $model;
    }

    /**
     * Возвращает новую модель
     *
     *  опционально:
     *      - можно задать данные модели
     *      - можно сохранить модель
     *      - можно отменить валидацию при сохранении
     *
     * @param array $params Массив аттрибутов и их данных
     * @param bool $save Сохранить модель?
     * @param bool $validation При сохранении валидировать модель?
     * @return ActiveRecord
     */
    public function createInstance( array $params = [], bool $save = false, bool $validation = true ): ActiveRecord
    {
        /** @var ActiveRecord $model */
        $model = new $this->class();

        if ( !empty($params) ) {
            $model->setAttributes( $this->getParams($params) );

            if ( $save ) {
                $model->save($validation);
            }
        }

        return $model;
    }



    // обработчики

    /**
     * Перебор в цикле всех моделей коллекции и вызов функции из аргумента с передачей модели в эту функцию
     *
     * @param callable $callback Анонимная функция в которую будет передаваться модель из массива `data`
     * @return self
     */
    public function foreach( callable $callback ): self
    {
        /** @var ActiveRecord $model */
        foreach ( $this->data as $model ){
            $callback( $model );
        }

        return $this;
    }

    /**
     * Обработка моделей из POST запроса
     *
     *  опционально:
     *      - можно задать callback функцию
     *
     * @param ?callable $callback Callback функция, которая будет вызвана
     * @return self
     */
    public function handler( ?callable $callback = null ): self
    {
        $this->loadModels();

        if ( $callback ) {
            $this->foreach($callback);
        }

        return $this;
    }

    /**
     * Сохранение всех моделей в коллекции.
     *      Вызов у всех моделей метода `save()`
     *
     *  опционально:
     *      - можно отменить валидацию при сохранении
     *
     * @param bool $validation При сохранении валидировать модель?
     * @return bool
     */
    public function save( bool $validation = true ): bool
    {
        foreach ( $this->data as $model ) {
            if ( !$model->save($validation) ) {
                return false;
            }
        }

        return true;
    }



    // Взаимодействие с формой

    /**
     * Установки для конструктора форм
     *
     *  $collection = new Kit( OrderItem::class );
     *  $collection->setFormConfig($form, $model);
     *
     * @param ActiveForm $form
     * @return void
     */
    public function setFormConfig( ActiveForm $form ): void
    {
        $this->form = $form;
    }

    /**
     * Конструктор полей который задаёт имя поля вида: `Model[{id}][attr]` / `Model['new'][attr][]`
     *
     * @param ActiveRecord $model модель
     * @param string $attr Имя аттрибута модели
     * @param string $method Метод поля ввода
     * @param array $params параметры для поля ввода
     * @return ActiveField
     */
    public function field( ActiveRecord $model, string $attr, string $method, array $params = []): ActiveField
    {
        $params = array_merge($params,[
            'name' => $this->generateName($model, $attr )
        ]);

        return $this->form->field( $model, $attr )->$method($params);
    }

    /**
     * Генерирует имя для полей ввода данных
     *
     * @param ActiveRecord $model
     * @param $attr
     * @return string
     */
    public function generateName( ActiveRecord $model, $attr ): string
    {
        if ( $model->isNewRecord )
        {
            $id = ( -1 * $this->i );
            $this->i++;

        } else {

            $id = $model->getAttribute('id');
        }

        return "{$this->class}[$id][$attr]";
    }



    // Getter

    /**
     * Возвращает данные для моделей.
     *
     * @param array $params Массив аттрибутов и их данных
     * @return array
     */
    private function getParams( array $params ): array
    {
        return $params[ $this->className ] ?? $params;
    }

    /**
     * Получение коллекции моделей
     *      Getter для внешнего использования
     *
     *  $collection = new Kit( OrderItem::class, Yii::$app->request->post() );
     *
     *  foreach( $collection->getData() as $model ) {
     *      $model->save();
     *  }
     * @return ActiveRecord[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Метод возвращает из `post` массив данных из ключа аналогичного имени класса `$this->className`
     *
     * @return ?array
     */
    public function getPostData(): ?array
    {
        return Yii::$app->request->post( $this->class );
    }






    // Дополнительный функционал

    /**
     * Удаление всех моделей из коллекции
     *
     * @return self
     */
    public function drop(): self
    {
        $this->data = [];

        return $this;
    }
}