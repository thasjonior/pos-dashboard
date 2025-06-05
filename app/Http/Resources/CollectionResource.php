<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Mail;

class CollectionResource extends JsonResource
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
            'date' => $this->date,
            'amount' => $this->amount,
            'receipt_id' => $this->receipt_id,
            'client' => new ClientResource($this->client),
            // 'machine'=> new MachineResource($this->machine), // Assuming you have a MailResource for the machine
            'machine' => [
                'id' => $this->machine_id,
                'name' => $this->machine ? $this->machine->name : null,
                'serial_number' => $this->machine ? $this->machine->serial_number : null,
            ],
            'items' => CollectionItemResource::collection($this->collectionItems),
        ];
    }
}