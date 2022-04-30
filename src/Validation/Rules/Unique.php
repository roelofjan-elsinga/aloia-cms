<?php

namespace AloiaCms\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;

class Unique implements Rule
{
    protected string $model;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $model = $this->createModel();

        $instance = $model->findById($value);

        return !$instance->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The model is not unique.';
    }

    /**
     * Create a new instance of the model.
     *
     * @return \AloiaCms\Models\Model
     */
    private function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }
}
