<?php

namespace Kaishiyoku\CreateUser\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new laravel user';

    /**°
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->askWithValidation('Enter user name', ['name' => $this->getValidationRule('name')]);
        $email = $this->askWithValidation('Enter user email', ['email' => $this->getValidationRule('email')]);
        $password = $this->secretWithValidation('Enter user password', ['password' => $this->getValidationRule('password')]);

        $model = config('createuser.model');

        $user = new $model();

        $user['name'] = $name;
        $user->email = $email;
        $user->password = Hash::make($password);

        $user->save();

        $this->info('New user created!');
    }

    private function askWithValidation($question, $rules, $secret = false)
    {
        $value = $secret ? $this->secret($question) : $this->ask($question);

        $validate = $this->validateInput($rules, $value);

        if ($validate !== true) {
            $this->error($validate);

            $value = $this->askWithValidation($question, $rules); // ?
        }

        return $value;
    }

    private function secretWithValidation($question, $rules)
    {
        return $this->askWithValidation($question, $rules, true);
    }

    private function validateInput($rules, $value)
    {
        $validator = Validator::make([key($rules) => $value], $rules);

        if ($validator->fails()) {
            return $error = $validator->errors()->first(key($rules));
        }

        return true;
    }

    private function getValidationRule($key)
    {
        return config('createuser.validation_rules.' . $key);
    }
}
