<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Jobs\DeleteItemsJob;

class LinkController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $links = [];
        if(!empty($request->query('state')))
        {
            $state = $request->query('state');
            switch($state) {
                case 'active':
                    $links = $request->user()->links;
                    break;
                case 'inactive':
                    $links = $request->user()->links()->onlyTrashed()->get();
                    break;
                default:
                    $links = $request->user()->links;
            }
        }
        else
            $links = $request->user()->links;
        return $this->successResponse($links);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            "url" => "string|required"
        ];
        $messages = [
            "url.string" => "URL must to be a string",
            "url.required" => "URL is required"
        ];
        $this->validate($request, $rules, $messages);
        // Create link
        $user = $request->user();
        $expiration_at = Carbon::now('UTC')->addDays(15);
        Log::info("expiration_at",["time"=>$expiration_at]);
        $link = $user->links()->create([
            "original_url" => $request->url,
            "expiration_at" => $expiration_at
        ]);
        return $this->successResponse([
            "shorten" => url('/link/'.$link->code)
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show($code)
    {
        $link = Link::where("code", $code)->firstOrFail();
        return $this->successResponse([
            "url" => $link->original_url
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Link $link)
    {
        DB::beginTransaction();
        try
        {
            $rules = [
                "url" => "string|required"
            ];
            $messages = [
                "url.string" => "URL must be a string",
                "url.required" => "URL is required"
            ];
            $this->validate($request, $rules, $messages);

            $link->update([
                "original_url" => $request->url
            ]);
            DB::commit();
            return $this->successResponse(true);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try
        {
            $link = Link::withTrashed()->findOrFail($id);

            if($request->has('hard'))
                $link->forceDelete();
            else
                $link->delete();

            DB::commit();
            return $this->successResponse([
                "deleted" => true
            ]);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Restore a deleted link
     */
    public function restore(Request $request, $link)
    {
        DB::beginTransaction();
        try
        {
            $all = Link::withTrashed()->find($link);
            $all->restore();
            DB::commit();
            return $this->successResponse([
                "restored" => true
            ]);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     *  Checks Batch Status
     */
    public function batchStatus($id)
    {
        $batch = Bus::findBatch($id);

        if(!$batch)
            return $this->errorResponse("Batch no encontrado", 404);

        return $this->successResponse([
            'id' => $batch->id,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'progress' => $batch->progress(),
            'finished' => $batch->finished()
        ]);
    }

    /**
     *  Deletes in bulk
     */
    public function deleteBulk(Request $request)
    {
        $items = collect($request->items);

        $batch = Bus::batch([])
            ->then(function (Batch $bt) {
                // Batch completado
                Log::info("Batch completado: {$bt->id}");
            })
            ->catch(function (Batch $bt, Throwable $e) {
                Log::error("Batch falló: {$bt->id}, error: {$e->getMessage()}");
            })
            ->finally(function (Batch $bt) {
                Log::info("Batch finalizado: {$bt->id}");
            })
            ->dispatch();

        $items->chunk(50)
              ->each(function ($chunk) use ($batch) {
                $batch->add(new DeleteItemsJob($chunk->toArray());
              });

        return $this->successResponse([
            "batch_id" => $batch->id,
            "message" => "Eliminación masiva en progreso. Puedes consultar el progreso del batch."
        ]);
    }
}
