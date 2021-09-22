<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Worker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Worker command.
     *
     * @var string
     */
    public $command;

    /**
     * Enable logging.
     *
     * @var bool
     */
    public $output_log;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $command, bool $output_log=false)
    {
        $this->command = $command;
        $this->output_log = $output_log;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cmd = x_tstr($this->command);
        $output_log = $this->output_log;
        $start = now()->format('Y-m-d H:i:s');
        $console = app('ConsoleService');
        $console->runExec($cmd, 1);
        $stop = now()->format('Y-m-d H:i:s');
        if ($output_log) {
            $json = json_encode([
                'exit' => $console->exit,
                'success' => $console->success,
                'error' => $console->error,
                'output' => $console->output,
            ]);
            $tmp = [];
            $ts = $start == $stop ? "[$start]" : "[$start - $stop]";
            $tmp[] = "$ts: $cmd";
            $tmp[] = sprintf('> exit=%s, success=%s, error=%s', $console->exit, $console->success, $console->error);
            if (x_is_list($console->output, 0)) {
                $tmp = array_merge($tmp, array_map(function ($item) {
                    return '> ' . x_tstr($item);
                }, $console->output));
            }
            $tmp = implode("\r\n", $tmp) . "\r\n";
            $this->save_log($tmp);
        }
    }

    //save log
    public function save_log($str)
    {
        $path = storage_path('logs/worker.log');

        return x_file_put($path, $str, FILE_APPEND);
    }
}
