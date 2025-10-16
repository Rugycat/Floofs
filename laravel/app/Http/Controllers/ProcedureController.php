<?php
namespace App\Http\Controllers;

use App\Models\Procedure;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    public function index()
    {
        return response()->json(Procedure::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'health_record_id'=>'required|integer|exists:health_records,id',
            'title'=>'required|string|max:200',
            'description'=>'nullable|string',
            'scheduled_at'=>'nullable|date',
            'status'=>'nullable|in:planned,done,canceled'
        ]);
        $proc = Procedure::create($validated);
        return response()->json($proc, 201);
    }

    public function show(Procedure $procedure)
    {
        return response()->json($procedure, 200);
    }

    public function update(Request $request, Procedure $procedure)
    {
        $validated = $request->validate([
            'title'=>'sometimes|required|string|max:200',
            'description'=>'nullable|string',
            'scheduled_at'=>'nullable|date',
            'status'=>'nullable|in:planned,done,canceled'
        ]);
        $procedure->update($validated);
        return response()->json($procedure, 200);
    }

    public function destroy(Procedure $procedure)
    {
        $procedure->delete();
        // returning 204 (no content) is OK per requirements
        return response()->json(null, 204);
    }

    // Hierarchical list: procedures for a given health record
    public function indexForRecord($recordId)
    {
        $list = Procedure::where('health_record_id', $recordId)->get();
        return response()->json($list, 200);
    }

    public function storeForRecord(Request $request, $recordId)
    {
        $request->merge(['health_record_id'=>$recordId]);
        return $this->store($request);
    }
}
