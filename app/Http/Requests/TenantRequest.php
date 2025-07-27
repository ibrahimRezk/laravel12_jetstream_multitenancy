<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Stancl\Tenancy\Database\Models\Domain;

class TenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $model = $this->id ?? null;  /// this refers to tenant model
        $passwordRule = $model ? ['nullable'] : ['required'];

        
        $userId = $model ? Tenant::find($model)->ownerId : '' ;
        // dd($userId);
        $subDomain = $model ? Tenant::find($model)->domains[0]->id : '' ;

        return [

            
            'name' => ['required', 'string', 'max:255'],
            
            
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($userId ?? null)],
                // 'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'subdomain' =>  ['required', 'string', 'alpha', 'max:255', Rule::unique(Domain::class, 'domain')->ignore($subDomain ?? null)],

                // 'password' => ['required', 'string', Password::default(), 'confirmed'],
                'password' => ['bail', ...$passwordRule, Password::defaults()],
                'password_confirmation' => ['bail', ...$passwordRule, 'same:password'],

                'plan_id' => ['required', 'exists:plans,id']
        ];
    }
}
