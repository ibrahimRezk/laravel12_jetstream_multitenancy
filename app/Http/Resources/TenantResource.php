<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
            'tenancy' => $this->tenancy,
            'tenancy_db_name' => $this->tenancy_db_name,
            'domains' => $this->domains,
            'hasActiveSubscription' => $this->hasActiveSubscription(),
            'isOnTrial' => $this->isOnTrial(),
            // 'canAccess' => $this->canAccess(),

            'users' => new UserResource($this->whenLoaded('users')),

            'subscription' => new TenantSubscriptionResource($this->whenLoaded('subscription')),
            'subscriptions' => TenantSubscriptionResource::collection($this->whenLoaded('subscriptions')),
            



                   'created_at' => $this->when($this->created_at, function () {
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
                   'updated_at' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
        ];
    }
}
