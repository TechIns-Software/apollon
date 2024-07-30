<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'business_id'=>$this->business_id,
            'driver'=>$this->driver->driver_name,
            'name'=>$this->name,
            'orders'=>DeliveryOrderResource::collection($this->deliveryOrder->sortBy('delivery_sequence')->sortBy('id')),
            "pdf_url"=>route('delivery_pdf',['id'=>$this->id]),
            'delivery_date'=>$this->delivery_date
        ];
    }
}
