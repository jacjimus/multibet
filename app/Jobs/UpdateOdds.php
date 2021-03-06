<?php

namespace App\Jobs;

use App\Services\ApiFootball\OddsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UpdateOdds implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $date;

    private array $data;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $data)
    {
        $this->date = $date;
        $this->data = $data;
    }

    public function uniqueVia()
    {
        return Cache::driver('redis');
    }

    public function uniqueId(): string
    {
        return md5((string) json_encode([$this->date => $this->data]));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = $this->date;

        try {
            (new OddsService())->updateOdds($date);
        } catch (Exception $e) {
            ray($e);
        }
    }
}
