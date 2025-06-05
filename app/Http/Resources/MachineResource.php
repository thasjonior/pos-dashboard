<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MachineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'serial_number' => $this->serial_number??"",
            'installation_date' => $this->installation_date,
            'is_active' => $this->is_active ? true : false,
            'description' => $this->description,
            'collector' => new UserResource($this->collector),
            'company' => new CompanyResource($this->company),
            'summary' => [
                "period" => "today",
                "revenue" => 1234567,
                "transactions" => 100,
            ]
        ];
    }
}