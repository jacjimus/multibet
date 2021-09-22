<?php

namespace App\Listeners;

use Exception;
use Illuminate\Database\Eloquent\Model;

class ModelHandler
{
    //event model
    public static function eventModel($data)
    {
        if (x_is_list($data, 0) && ($model = $data[0]) instanceof Model) {
            return $model;
        }

        throw new Exception('Invalid event data model!');
    }

    //saving
    public function handleSaving($eventName, array $data)
    {
        $model = static::eventModel($data);

        return x_validate_model($model);
    }

    //saved
    public function handleSaved($eventName, array $data)
    {
        $model = static::eventModel($data);

        //global uploads - move & update
        if (x_is_list($uploads = x_globals_get('uploads'), 0)) {

            //check uploads
            foreach ($model->toArray() as $key => $val) {
                if (in_array($val, $uploads)) {

                    //upload service
                    $s = app('UploadService');

                    //check upload file
                    if (x_is_file($path = $s->storeDir($val))) {
                        $new_path = $s->path(basename($path));
                        $new_path = dirname($new_path) . '/' . $model->getTable() . '/' . $model->getId() . '/' . $key . '/' . basename($new_path);

                        //move temp file
                        if (x_move($path, $s->storeDir($new_path), $overwrite=1)) {

                            //update model
                            $model->{$key}=$new_path;
                            $model->saveQuietly();

                            //delete temp file
                            x_file_delete($path);
                        }
                    }

                    //upload service cleanup
                    $s->cleanup();
                }
            }
        }
    }

    //creating
    public function handleCreating($eventName, array $data)
    {
        $model = static::eventModel($data);

        //set timestamp user
        if ($model->getOptions('timestamp_user') && ($user = app('UserService')->getUser())) {
            $model->created_by = $user->id;
            $model->updated_by = $user->id;
        }
    }

    //updating
    public function handleUpdating($eventName, array $data)
    {
        $model = static::eventModel($data);

        //set timestamp user
        if ($model->getOptions('timestamp_user') && ($user = app('UserService')->getUser())) {
            $model->updated_by = $user->id;
        }
    }

    //deleted
    public function handleDeleted($eventName, array $data)
    {
        $model = static::eventModel($data);

        //set timestamp user
        if ($model->getOptions('softdelete_user') && ($user = app('UserService')->getUser())) {
            $model->deleted_by = $user->id;
            $model->saveQuietly();
        }
    }

    //restored
    public function handleRestored($eventName, array $data)
    {
        $model = static::eventModel($data);

        //unset softdelete
        if (isset($model->deleted_at) || isset($model->deleted_by)) {
            if (isset($model->deleted_by)) {
                $model->deleted_by = null;
            }
            if (isset($model->deleted_at)) {
                $model->deleted_at = null;
            }
            $model->saveQuietly();
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     *
     * @return void
     */
    public function subscribe($events)
    {
        //event handlers
        $handlers = [
            //model events
            'eloquent.retrieved' => null,
            'eloquent.creating' => 'handleCreating',
            'eloquent.created' => null, //'handleCreated',
            'eloquent.updating' => 'handleUpdating',
            'eloquent.updated' => null,
            'eloquent.saving' => 'handleSaving',
            'eloquent.saved' => 'handleSaved',
            'eloquent.deleting' => null, //'handleDeleting',
            'eloquent.deleted' => 'handleDeleted',
            'eloquent.restoring' => null, //'handleRestoring',
            'eloquent.restored' => 'handleRestored',
            'eloquent.replicating' => null,

            //use Fico7489\Laravel\Pivot\Traits\PivotEventTrait
            //https://github.com/fico7489/laravel-pivot
            'eloquent.pivotAttaching' => null,
            'eloquent.pivotAttached' => null, //'handlePivotAttached',
            'eloquent.pivotDetaching' => null,
            'eloquent.pivotDetached' => null, //'handlePivotDetached',
            'eloquent.pivotUpdating' => null,
            'eloquent.pivotUpdated' => null, //'handlePivotUpdated',
        ];

        //register listeners
        $self = static::class;
        foreach ($handlers as $event => $handler) {
            if (!$handler) {
                continue;
            }
            $events->listen("$event: *", [$self, $handler]);
        }
    }
}
