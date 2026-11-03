<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SubscribeToBeehiiv implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $email) {}

    public function handle(): void
    {
        if (! config('services.beehiiv.enabled')) {
            return;
        }

        $publication = config('services.beehiiv.publication_id');

        Http::withToken((string) config('services.beehiiv.key'))
            ->post("https://api.beehiiv.com/v2/publications/{$publication}/subscriptions", [
                'email' => $this->email,
                'reactivate_existing' => false,
                'send_welcome_email' => false,
                'utm_source' => 'lunar-society',
            ])
            ->throw();
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }
}
