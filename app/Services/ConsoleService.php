<?php

namespace App\Services;

use Artisan;
use Exception;

class ConsoleService
{
    //vars
    public $exit;

    public $error;

    public $success;

    public $output;

    //get php binary
    public function php()
    {
        return config('app.phpbin', 'php');
    }

    //php command
    public function cmd(string $command=null, bool $artisan=true, bool $php=true)
    {
        $_param = function ($str) {
            $str = x_tstr($str);
            if (strpos($str, ' ') !== false) {
                $str = sprintf('"%s"', $str);
            }

            return $str;
        };
        $cmd = [];
        if ($php) {
            $cmd[] = $_param($this->php());
        }
        if ($artisan) {
            $cmd[] = $_param(base_path('artisan'));
        }
        if (($command = x_tstr($command)) != '') {
            $cmd[] = $command;
        }

        return implode(' ', $cmd);
    }

    //reset vars
    private function reset()
    {
        $this->success = false;
        $this->output = null;
        $this->exit = null;
        $this->error = null;
    }

    //run system
    public function runExec(string $command, bool $throwable=false)
    {
        //reset vars
        $this->reset();

        //exec command
        try {
            //call exec
            if (exec($command, $output, $exit) === false) {
                throw new Exception("Exec failure! ($command)");
            }

            //set vars (success)
            $this->success = true;
            $this->output = $output;
            $this->exit = $exit;
        }

        //catch exception
        catch (Exception $e) {
            $this->error = x_err($e); //set error
            if ($throwable) {
                throw $e;
            } //throw
        }

        //return self
        return $this;
    }

    //run artisan
    public function runArtisan(string $command, bool $throwable=false)
    {
        //reset vars
        $this->reset();

        //exec command
        try {
            //call artisan
            $exit = Artisan::call($command);

            //set vars (success)
            $this->success = true;
            $this->output = Artisan::output();
            $this->exit = $exit;
        }

        //catch exception
        catch (Exception $e) {
            $this->error = x_err($e); //set error
            if ($throwable) {
                throw $e;
            } //throw
        }

        //return self
        return $this;
    }

    //call artisan command i.e. artisan('queue:table')
    public function artisan(string $cmd, int $expected_exit_code=0)
    {
        $this->_type = 'artisan';

        //call artisan command
        $exit = Artisan::call($cmd);

        //check expected exit code
        if ($expected_exit_code != -1 && $exit != $expected_exit_code) {
            throw new Exception(sprintf('Unexpected artisan exit code "%s" (%s)!', $exit, $cmd));
        }

        //result (exit code, output)
        return [
            'cmd' => $cmd,
            'exit' => $exit,
            'output' => $this->output()
        ];
    }
}
