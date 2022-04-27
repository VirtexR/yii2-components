<?php

namespace andy87\tests;

use andy87\test\models\Test;
use andy87\yii_components\Kit;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

/**
 *
 */
class TestKit
{
    const TEST_CLASS = Test::class;

    /** @var ?Kit  */
    private ?Kit $kit;

    /**
     * @return array
     */
    public function start(): array
    {
        $result = [];

        $tests = [
            'create',
            'create_with_data',
            'loadModelsPostWithClass',
            'loadModelsPostWithoutClass',
            'addModelList',
            'insertModel',
            'findModels',
            'constructModel',
            'createInstance',
            'foreach',
            'handler',
            'save',
            'setupForm',
            'generateName',
            'field',
            'drop',
        ];

        foreach ( $tests as $test )
        {
            $result[ $test ] = $this->{$test}();
            $this->kit->drop();
        }

        return $result;
    }

    /**
     * @return string
     */
    private function create(): string
    {
        try {

            $this->kit = new Kit( self::TEST_CLASS );

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function create_with_data(): string
    {
        try
        {
            $this->kit = new Kit( self::TEST_CLASS, [
                'status_id' => Test::STATUS_4
            ]);

            if ( $this->kit->getData()[0]['status_id'] !== Test::STATUS_4 )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function loadModelsPostWithClass(): string
    {
        try {
            $post = [
                $this->kit->className => [
                    [ 'id' => 4, 'status_id' => Test::STATUS_4],
                    [ 'id' => 5, 'status_id' => Test::STATUS_5],
                ]
            ];

            $this->kit->loadModels($post);

            $check = [
                0 => Test::STATUS_4,
                1 => Test::STATUS_5,
            ];

            foreach ( $check as $id => $status )
            {
                /** @var Test $test */
                $test = $this->kit->getData()[$id];

                if ( $test->status_id !== $status )
                {
                    return 'error: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function loadModelsPostWithoutClass(): string
    {
        try {
            $post = [
                [
                    [ 'id' => 4, 'status_id' => Test::STATUS_4],
                    [ 'id' => 5, 'status_id' => Test::STATUS_5],
                ]
            ];

            $this->kit->loadModels($post);

            $check = [
                0 => Test::STATUS_4,
                1 => Test::STATUS_5,
            ];

            foreach ( $check as $id => $status )
            {
                /** @var Test $test */
                $test = $this->kit->getData()[$id];

                if ( $test->status_id !== $status )
                {
                    return 'error: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function addModelList(): string
    {
        try {

            $data = [
                $this->kit->createInstance([ 'id' => 4, 'status_id' => Test::STATUS_4]),
                $this->kit->createInstance([ 'id' => 5, 'status_id' => Test::STATUS_5]),
            ];

            $this->kit->addModelList($data);

            $check = [
                0 => Test::STATUS_4,
                1 => Test::STATUS_5,
            ];

            foreach ( $check as $id => $status )
            {
                /** @var Test $test */
                $test = $this->kit->getData()[$id];

                if ( !($test instanceof Test) || $test->status_id !== $status )
                {
                    return 'error: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function insertModel(): string
    {
        try {

            $this->kit->insertModel(1, [ 'status_id' => Test::STATUS_2]);

            $test = $this->kit->getData()[0];

            if ( !($test instanceof Test) || $test->status_id !== Test::STATUS_2 )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function findModels(): string
    {
        try {

            $ids = [1,2,3];

            $this->kit->findModels(['id' => $ids]);

            if ( count($this->kit->getData()) !== count($ids) )
            {
                return 'error 1: ' . __METHOD__;
            }

            /** @var Test $test */
            foreach ( $this->kit->getData() as $test )
            {
                if ( !($test instanceof Test) )
                {
                    return 'error 2: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function constructModel(): string
    {
        try {

            $this->kit->constructModel(['id' => 4, 'status_id' => Test::STATUS_4]);

            $model = $this->kit->getData()[0];

            if ( !($model instanceof Test) || $model->status_id !== Test::STATUS_4 )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function createInstance(): string
    {
        try {

            $model = $this->kit->createInstance(['id' => 4, 'status_id' => Test::STATUS_4]);

            if ( count($this->kit->getData()) )
            {
                return 'error 1: ' . __METHOD__;
            }

            if ( !($model instanceof Test) || $model->status_id !== Test::STATUS_4 )
            {
                return 'error 2: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function foreach(): string
    {
        try {

            $data = [
                $this->kit->createInstance([ 'id' => 1, 'status_id' => Test::STATUS_1]),
                $this->kit->createInstance([ 'id' => 2, 'status_id' => Test::STATUS_2]),
                $this->kit->createInstance([ 'id' => 3, 'status_id' => Test::STATUS_3]),
            ];

            $this->kit->addModelList($data);

            $this->kit->foreach(function ( Test $test){
                $test->status_id = Test::STATUS_4;
            });

            foreach ( $this->kit->getData() as $test )
            {
                if ( !($test instanceof Test) || $test->status_id !== Test::STATUS_4 )
                {
                    return 'error: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */

    private function handler(): string
    {
        try {

            $data = [
                $this->kit->createInstance([ 'id' => 1, 'status_id' => Test::STATUS_1]),
                $this->kit->createInstance([ 'id' => 2, 'status_id' => Test::STATUS_2]),
                $this->kit->createInstance([ 'id' => 3, 'status_id' => Test::STATUS_3]),
            ];

            $this->kit->addModelList($data);

            $this->kit->handler(function (Test $test){
                $test->status_id = Test::STATUS_4;
            });

            foreach ( $this->kit->getData() as $test )
            {
                if ( !($test instanceof Test) || $test->status_id !== Test::STATUS_4 )
                {
                    return 'error: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }
    /**
     * @return string
     */
    private function save(): string
    {
        try {

            $data = [
                $this->kit->createInstance([ 'id' => 1, 'status_id' => Test::STATUS_1]),
                $this->kit->createInstance([ 'id' => 2, 'status_id' => Test::STATUS_2]),
                $this->kit->createInstance([ 'id' => 3, 'status_id' => Test::STATUS_3]),
            ];

            $this->kit->addModelList($data);

            $this->kit->foreach(function ( Test $test){
                $test->status_id = Test::STATUS_4;
            });

            $this->kit->save();

            foreach ( $this->kit->getData() as $test )
            {
                if ( !($test instanceof Test) || $test->status_id !== Test::STATUS_4 )
                {
                    return 'error: ' . __METHOD__;
                }
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }


    /**
     * @return string
     */
    private function setupForm(): string
    {
        try {

            $form = new ActiveForm();

            $this->kit->setupForm($form);

            if ( !($this->kit->form instanceof ActiveForm) )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function generateName(): string
    {
        try {

            $model = $this->kit->createInstance(['id' => 1, 'status_id' => Test::STATUS_4]);

            $name = $this->kit->generateName($model, 'id');

            $currentName = "{$this->kit->className}[{$model->id}][id]";

            if ( $name !== $currentName )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function field(): string
    {
        try {

            $model = $this->kit->createInstance(['id' => 4, 'status_id' => Test::STATUS_4]);

            $field = $this->kit->field($model, 'id', 'textInput');

            if ( !($field instanceof ActiveField) )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    private function drop(): string
    {
        try {

            $data = [
                $this->kit->createInstance([ 'id' => 4, 'status_id' => Test::STATUS_4]),
                $this->kit->createInstance([ 'id' => 5, 'status_id' => Test::STATUS_5]),
            ];

            $this->kit->addModelList($data);
            $this->kit->drop();

            if ( !empty($this->kit->getData()) )
            {
                return 'error: ' . __METHOD__;
            }

            return 'ok';

        } catch ( \Exception $e ) {

            return $e->getMessage();
        }
    }






}