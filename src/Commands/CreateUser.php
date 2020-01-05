<?php

namespace Kaishiyoku\CreateUser\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'user:create {--ignore=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new laravel user.';

    /**°
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ignoredFields = array_filter(explode(',', $this->option('ignore')), function ($value) {
            return !empty($value);
        });

        $fields = collect(config('createuser.fields'))->filter(function ($field, $key) use ($ignoredFields) {
            return !in_array($key, $ignoredFields);
        });

        $model = config('createuser.model');

        $user = new $model();

        foreach ($fields as $key => $field) {
            $modifierFn = $field['modifier_fn'];
            $value = $this->askWithValidation('Enter user ' . $key, [$key => $field['validation_rules']], $field['secret']);

            $user[$key] = $modifierFn ? call_user_func($modifierFn, $value) : $value;
        }

        $user->save();

        $postCreationFn = config('createuser.post_creation_fn');

        $postCreationFn($user);

        $this->info('New user created.');

        return $user->id;
    }

    private function askWithValidation($question, $rules, $secret = false)
    {
        $value = $secret ? $this->secret($question) : $this->ask($question);

        $validate = $this->validateInput($rules, $value);

        if ($validate !== true) {
            $this->error($validate);

            $value = $this->askWithValidation($question, $rules);
        }

        return $value;
    }

    private function validateInput($rules, $value)
    {
        $validator = Validator::make([key($rules) => $value], $rules);

        if ($validator->fails()) {
            return $error = $validator->errors()->first(key($rules));
        }

        return true;
    }
}
