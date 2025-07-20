<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePlanResource extends JsonResource
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
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'interval' => $this->interval,
            'features' => $this->features,
            'trial_days' => $this->trial_days,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,

                   'created_at' => $this->when($this->created_at, function () {
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
                   'updated_at' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
        ];
    }
}
