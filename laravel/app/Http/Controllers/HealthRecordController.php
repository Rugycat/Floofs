<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use Illuminate\Http\Request;

class HealthRecordController extends Controller
{
    public function index()
    {
        return HealthRecord::all();
    }

    public function store(Request $request)
    {
        $record = HealthRecord::create($request->all());
        return response()->json($record, 201);
    }

    public function show(HealthRecord $healthRecord)
    {
        return $healthRecord;
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
        $healthRecord->update($request->all());
        return response()->json($healthRecord, 200);
    }

    public function destroy(HealthRecord $healthRecord)
    {
        $healthRecord->delete();
        return response()->json(null, 204);
    }
    public function indexForPet($petId)
{
    $records = \App\Models\HealthRecord::where('pet_id',$petId)->get();
    return response()->json($records, 200);
}

public function storeForPet(Request $request, $petId)
{
    $request->merge(['pet_id' => $petId]);
    return $this->store($request);
}

}
