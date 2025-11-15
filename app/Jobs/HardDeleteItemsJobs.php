<?php

namespace App\Jobs;

use App\Models\Link;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HardDeleteItemsJobs implements ShouldQueue
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
        Link::onlyTrashed()->whereIn('id', $this->items)->forceDelete();
    }
}
