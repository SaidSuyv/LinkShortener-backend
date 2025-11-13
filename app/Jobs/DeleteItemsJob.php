<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Link;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

class DeleteItemsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels, Batchable;

    public array $items;
    /**
     * Create a new job instance.
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Link::whereIn('id', $this->items)->delete();
    }
}
