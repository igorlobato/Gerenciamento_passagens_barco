<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $logs = Log::orderBy('created_at', 'desc')
                ->paginate(20);
            return response()->json($logs);
        }catch (\Exception $e){
            return response()->json(['error' => 'Erro ao obter logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $log = Log::find($id);
        if(!$log) {
            return reponse()->json(['error' => 'Log não encontrado'], 404);
        }

        $authUser = JWTAuth::user();
        if (!$authUser || $authUser->id != $id) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        } else{
            return response()->json([$log], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
