<?php

//get current pid
function x_pid()
{
    return getmypid();
}

//get pid process
function x_pid_get($pid, &$cmd=null)
{
    $cmd = null;
    if (($pid = x_int($pid, 1)) <= 0) {
        return;
    }
    $console = app('ConsoleService');
    $console->runExec("ps -p $pid -opid=,cmd=", 0);
    $cmd = x_is_list($console->output, 0) ? x_tstr($console->output[0]) : null;
    if (!is_null($cmd)) {
        $cmd = x_tstr(str_replace("$pid", '', $cmd));

        return $pid;
    }

    return false;
}

//queue worker
function x_worker($cmd, bool $output_log=false, bool $artisan=true, bool $php=true)
{
    $cmd = app('ConsoleService')->cmd($cmd, $artisan, $php);
    \App\Jobs\Worker::dispatch($cmd, $output_log);

    return x_queue_work();
}

//queue work
function x_queue_work()
{
    $console = app('ConsoleService');
    $key = 'x_queue_work';
    x_cache_delete($key);
    $pid = (int) x_cache_get($key);
    if ($pid && ($tmp = x_pid_get($pid))) {
        return $tmp;
    }
    x_cache_delete($key);
    $cmd = $console->cmd('queue:work --daemon --stop-when-empty > /dev/null & echo $!');
    $console->runExec($cmd, 1);
    if ($console->success) {
        $pid = x_is_list($console->output, 0) ? (int) $console->output[0] : 0;
        if ($pid > 0) {
            x_cache_set($key, $pid);

            return $pid;
        }
    }

    return false;
}
