<?php

namespace App\Http\Controllers;

class ModelsController extends Controller
{
    //debug
    private function debug($name, $path)
    {
        dump([
            'ModelsController' => $name,
            'path' => $path,
        ]);
    }

    //show
    public function show($path)
    {
        $this->debug('show', $path);
    }

    //create
    public function create($path)
    {
        $this->debug('create', $path);
    }

    //update
    public function update($path)
    {
        $this->debug('update', $path);
    }

    //delete
    public function delete($path)
    {
        $this->debug('delete', $path);
    }

    //restore
    public function restore($path)
    {
        $this->debug('restore', $path);
    }

    //destroy
    public function destroy($path)
    {
        $this->debug('destroy', $path);
    }
}
