<?php

namespace App\Jobs;

use App\Services\ApiFootball\OddsService;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Nette\Utils\JsonException;

class UpdateOdds implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $date;

    private array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $data)
    {
        $this->date = $date;
        $this->data = $data;
        // $this->onQueue('odds_update');
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return DateTime
     */
    public function retryUntil(): Datetime
    {
        return now()->addMinutes(5);
    }

    public function uniqueVia()
    {
        return Cache::driver('redis');
    }

    public function uniqueId(): string
    {
        return md5((string) json_encode($this->data));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = $this->date ?? date('Y-m-d');

        try {
            (new OddsService())->updateOdds($date);
        } catch (GuzzleException $e) {
        } catch (JsonException $e) {
            Log::error($e->getMessage());
        }
    }
}
