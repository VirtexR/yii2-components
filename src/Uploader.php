<?php /**
 * @author Andrey and_y87 Kidin
 * @url https://github.com/andy87/yii2-components
 */

namespace andy87\yii_components;

use Yii;
use Exception;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Помощник загрузки файлов.
 *
 * @property Model $model Модель/форма для которой загружается файл
 * @property string $attr_upload_file Имя свойства класса модели/формы с типом [type="file"]
 * @property string $attr_model Имя атрибута модели в которой пишется информация о загруженном файле
 * @property string $upload_dir Директория в которрую загружается файл
 * @property string $file_name Сгенерированное имя файла
 * @property string $file_path Сгенерированный путь расположения файла
 */
class Uploader
{
    /** @var Model $model Модель/форма для которой загружается файл */
    protected Model $model;

    /** @var string $attr_upload_file Имя свойства класса модели/формы с типом [type="file"] */
    protected string $attr_upload_file;

    /** @var string $attr_model Имя атрибута модели в которой пишется информация о загруженном файле */
    protected string $attr_model;

    /** @var string $upload_dir Директория в которую загружается файл */
    protected string $upload_dir;



    /** @var string $file_name Сгенерированное имя файла */
    protected string $file_name;

    /** @var string $file_path Сгенерированный путь расположения файла */
    protected string $file_path;



    /**
     * @throws Exception
     */
    function __construct( Model $model, array $attributes, string $upload_dir )
    {
        $this->model = $model;

        $this->attr_upload_file = array_key_first( $attributes );
        $this->attr_model = $attributes[ $this->attr_upload_file ];

        $exception = null;

        // Проверка наличия аттрибута у модели
        if ( !$model->hasProperty($this->attr_upload_file) ) $exception = $this->attr_upload_file;
        if ( !$model->hasProperty($this->attr_model) ) $exception = $this->attr_model;

        if ( $exception )
            $exception = "Uploader error: #1 attr `$exception` not found ";

        $this->upload_dir = Yii::getAlias($upload_dir);

        // Проверка наличия директории для загрузки
        if ( !is_dir($this->upload_dir) )
            $exception = "Uploader error: #2 `uploadDir` not found. \r\n $this->upload_dir";

        if ( $exception ) throw new Exception( $exception );
    }



    /**
     * Метод возвращает значение для аттрибута модели/формы, после успешной загрузки файла
     *
     *      * При необходимости можно переопределить
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function setAttrModel( UploadedFile $file ): bool
    {
        $this->model->{$this->attr_model} = $this->file_name;

        return $this->model->save();
    }

    /**
     * Генерация имени файла.
     *
     *      * При необходимости можно переопределить
     *
     * @return string
     */
    public function getFileName(): string
    {
        return md5(FileUploader . phpmicrotime());
    }

    /**
     * Получение дириктории в котороую будет загружен файл
     *
     *      * При необходимости можно переопределить
     *
     * @return string
     */
    public function getUploadDir(): string
    {
        return $this->upload_dir;
    }

    /**
     * запуск процесса загрузки одного файла
     *
     *      * При необходимости можно переопределить ( но, наверное, не желательно... )
     *
     * @return bool
     */
    public function upload(): bool
    {
        $this->model->{$this->attr_upload_file} = UploadedFile::getInstance( $this->model, $this->attr_upload_file );

        return $this->fileUploadProcess( $this->model->{$this->attr_upload_file} );
    }

    /**
     * запуск процесса загрузки нескольких файлов
     *
     *      * При необходимости можно переопределить ( но, наверное, не желательно... )
     *
     * @return bool
     */
    public function uploadMultiply(): bool
    {
        $this->model->{$this->attr_upload_file} = UploadedFile::getInstances( $this->model, $this->attr_upload_file );

        return $this->fileUploadProcess( $this->model->{$this->attr_upload_file} );
    }



    /**
     * процесс загрузки файла
     *
     * @param ?UploadedFile|UploadedFile[] $uploadedFile
     * @return bool
     */
    protected function fileUploadProcess( mixed $uploadedFile ): bool
    {
        $result = false;

        if ( $uploadedFile )
        {
            if ( !is_array($uploadedFile) ) $uploadedFile = [ $uploadedFile ];

            foreach ( $uploadedFile as $file )
            {
                // генерация уникального имени файла, в рамках директории загрузки
                do
                {
                    $this->file_name = $this->getFileName() . '.' . $file->getExtension();
                    $this->file_path = $this->getFilePath( $this->file_name );

                } while ( $this->is_file_exists( $this->file_name ) );

                try {

                    Yii::$app->db->beginTransaction();

                    //загрузка
                    if ( $file->saveAs( $this->file_path ) )
                    {
                        // задаётся значение атрибуту модели

                        if ( $this->setAttrModel( $file ) )
                        {
                            $result = true;

                            Yii::$app->db->transaction->commit();

                        } else {

                            $this->setError( $this->model );

                            Yii::$app->db->transaction->rollBack();

                            $this->deleteFile();
                        }
                    }

                } catch ( Exception $e ) {

                    Yii::$app->session->setFlash('error', $e->getMessage() );

                    Yii::$app->db->transaction->rollBack();

                    $this->deleteFile();
                }
            }
        }

        return $result;
    }

    /**
     * Получение полного пути к файлу
     *
     * @param string $fileName
     * @return string
     */
    protected function getFilePath( string $fileName ): string
    {
        return $this->getUploadDir() . $fileName;
    }

    /**
     * Проверка существования файла
     *
     * @param string $fileName
     * @return bool
     */
    protected function is_file_exists( string $fileName ): bool
    {
        return file_exists( $this->getFilePath($fileName) );
    }

    /**
     * Удление файла
     */
    protected function deleteFile()
    {
        if ( $this->is_file_exists( $this->file_name ) )
        {
            unset( $this->file_path );
        }
    }

    /**
     * @param Model $model
     */
    protected function setError( Model $model )
    {
        foreach ($model->getFirstErrors() as $message)
        {
            Yii::$app->session->setFlash('error', $message);
            break;
        }
    }
}