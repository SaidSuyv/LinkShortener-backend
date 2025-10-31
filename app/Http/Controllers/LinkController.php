<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Support\Facades\DB;

class LinkController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
        $link = $user->links()->create([
            "original_url" => $request->url
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
                "url.string" => "URL muwt be a string",
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
    public function destroy(Link $link)
    {
        DB::beginTransaction();
        try
        {
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
}
