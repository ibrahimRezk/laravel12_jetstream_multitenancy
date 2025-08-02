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

        $owner = new UserResource($this->whenLoaded('owner'));
        $subscription = new TenantSubscriptionResource($this->whenLoaded('subscription'));
        $plan = new PlanResource($this->whenLoaded('plan'));
        $users =  UserResource::collection($this->whenLoaded('users'));
        $subscriptions =  TenantSubscriptionResource::collection($this->whenLoaded('subscriptions'));
        
        $curerentSubscription = $this->currentSubscription();

        return [
            'id' => $this->id,
            'tenancy' => $this->tenancy,
            'tenancy_db_name' => $this->tenancy_db_name,
            'domains' => $this->domains,
            'hasActiveSubscription' => $this->hasActiveSubscription(),
            'isOnTrial' => $this->isOnTrial(),
            // 'canAccess' => $this->canAccess(),

            'users' => $users ,
            'owner' => $owner,
            'subscription' => $subscription,
            'subscriptions' => $subscriptions,
            'currentSubscriptions' => $curerentSubscription,
            

            /// to show all items as one item not nested item  these lines added :
            'name' => $owner->name,
            'email' => $owner->email,
            'status' => $curerentSubscription->status ?? '',
            'plan' => $plan->name ?? '',
            'interval' => $plan->interval ?? '',
            'price' => $curerentSubscription->price ?? '',
            'ends_at' =>$curerentSubscription?->ends_at?->isoFormat('Do MMMM YYYY , h:mm a') ?? '',
            'trial_ends_at' =>  $curerentSubscription?->trial_ends_at?->isoFormat('Do MMMM YYYY , h:mm a') ?? '',
///////////////////////////////////////////////////////////////////////////

                   'created_at' => $this->when($this->created_at, function () {
                return $this->created_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
                   'updated_at' => $this->when($this->updated_at, function () {
                return $this->updated_at->isoFormat('Do MMMM YYYY , h:mm a');
            }),
        ];
    }
}
