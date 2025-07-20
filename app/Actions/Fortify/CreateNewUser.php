<?php

namespace App\Actions\Fortify;

use App\Models\User;
// use App\Models\Tenant;
use App\Models\Tenant;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'subdomain' => ['required', 'alpha', 'max:255' , 'unique:domains,domain' ], // 'unique:domains' not working if we save subdomain with the full url because it bring for exampl eee.site.com not only eee
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            ])->validate();
            $user =  User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);
            
            

            $tenant = Tenant::create(
                [
                    'tenancy_db_name' => $input['subdomain']  // must add this part other wise duplicate name error will appear
                    ]
                );


        $tenant->domains()->create([
            'domain' => $input['subdomain'] // // if you use initializebysubdomain middleware in tenant.php
            // 'domain' => 'foo' // tobe removed it is here temporarly because laravel herd url can not read subdomaain directly we have to add it manualy in C:\Windows\System32\drivers\etc

            // 'domain' => $input['subdomain'] . '.' . config('tenancy.central_domains')[0] // if you use initializebydomain middleware in tenant.php
        ]);

        $user->tenants()->attach($tenant->id);

        event(new Registered($user));


        return $user;
    }
}
