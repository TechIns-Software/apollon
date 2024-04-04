<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'description' => $this->description,
            'business_id'=>$this->business_id,
            'client_id'=>$this->client_id,
            'status'=>$this->status,
            'items' => ProductOrderResource::collection($this->products)
        ];
    }
}
