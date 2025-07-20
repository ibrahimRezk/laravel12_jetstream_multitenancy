<?php

namespace App\Http\Resources;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantSubscriptionResource extends JsonResource
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
            'tenant_id' => $this->tenant_id,
            'status' => $this->status,
            'price' => $this->price,
            'is_active' => $this->isActive(),
            'on_trial' => $this->onTrial(),
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'ends_at' => $this->ends_at,
            // 'trial_ends_at' => $this->trial_ends_at,
            
            'created_at' => $this->when($this->created_at, function () {
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            'updated_at' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            'ends_at' => $this->when($this->ends_at, function () {
                return $this->ends_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            'trial_ends_at' => $this->when($this->trial_ends_at, function () {
                return $this->trial_ends_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
            
            
            'purchasePlan' => new PurchasePlanResource($this->whenLoaded('purchasePlan')),

            // 'purchasePlan' => $this->whenLoaded('purchasePlan') ??  '',

        ];    }
}
