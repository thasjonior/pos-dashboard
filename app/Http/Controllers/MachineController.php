<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Controllers\BaseController;
use App\Http\Resources\MachineResource; // Assuming this is the correct namespace for the resource
use Illuminate\Support\Facades\Log;

class MachineController extends BaseController
{


    //index
    public function index()
    {
        $machines = Machine::latest('created_at')->get();
        return $this->sendResponse(
            MachineResource::collection($machines),
            'Machines retrieved successfully'
        );
    }

    //store
    public function store(Request $request)
    {

        // Get machine data from request
        $machine = $request->all();
        Log::info($request->all());

        try {
            // Get or create collector using BaseService instance
            $baseService = new BaseService();
            $collector = $baseService->getOrCreateCollector(
                [
                    "collector_name" => $machine['collector_name'],
                    "collector_phone" => $machine['collector_phone'],
                    "machine_name" => $machine['name'],
                    "password" => $machine['password']
                ]
            );

            $machine['collector_id'] = $collector->id;

            if ($this->isMachineNameExisting($request)) {
                return $this->sendError('MACHINE_NAME_EXISTS', 'Machine name already exists', 422);
            }

            $model = Machine::create($machine);
        } catch (\Throwable $th) {
            return $this->sendError('SERVER_ERROR', $th->getMessage());
        }

        return $this->sendResponse(
            new MachineResource($model),
            'Machine registered successfully'
        );
        // Logic to store a new machine
    }

    //validateMachine
    public function isMachineNameExisting(Request $request): bool
    {
        // Logic to check if machine name already exists
        $machineData = $request->all();
        $machine = Machine::where('name', $machineData['name'])->first();

        return $machine ? true : false;
    }


    //show
    public function show($id)
    {
        // Logic to retrieve a specific machine by ID
        return response()->json(['message' => 'Machine retrieved successfully', 'id' => $id]);
    }
}
