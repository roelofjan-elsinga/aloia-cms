<?php

namespace AloiaCms\Auth;

use AloiaCms\Models\Contracts\ModelInterface;
use AloiaCms\Models\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Model implements Authenticatable
{
    protected $folder = 'users';

    protected $required_fields = [
        'identifier'
    ];

    public function findById(string $file_name): ModelInterface
    {
        $instance = new static();

        $instance->setFileName($file_name);
        $instance->set('identifier', $file_name);

        return $instance;
    }

    public static function find(string $file_name): ModelInterface
    {
        $instance = new static();
        $instance->setFileName($file_name);
        $instance->set('identifier', $file_name);

        return $instance;
    }

    public function getAuthIdentifierName()
    {
        return 'identifier';
    }

    public function getAuthIdentifier()
    {
        return $this->filename();
    }

    public function getAuthPassword()
    {
        return $this->get('password');
    }

    public function getRememberToken()
    {
        return $this->get('remember_token');
    }

    public function setRememberToken($value)
    {
        return $this->set('remember_token', $value);
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
