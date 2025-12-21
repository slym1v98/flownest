<?php

namespace Modules\User\Actions;

use App\Traits\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers as CreatesNewUsersContract;
use Modules\User\Models\User;

class CreatesNewUsers implements CreatesNewUsersContract
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
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class),
            ],
            'email'    => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::query()->create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'username' => $input['username'],
            'password' => $input['password'],
        ]);
    }
}
