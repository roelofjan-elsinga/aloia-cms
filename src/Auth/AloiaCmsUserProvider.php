<?php

namespace AloiaCms\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class AloiaCmsUserProvider implements UserProvider
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Create a new Aloia CMS user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @return void
     */
    public function __construct(HasherContract $hasher, string $model)
    {
        $this->hasher = $hasher;
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {
        $user = $this
            ->createModel()
            ->findById($identifier);

        if ($user->exists()) {
            return $user;
        }

        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        $user = $this
            ->createModel()
            ->findById($identifier);

        if (!$user->exists()) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }

    public function retrieveByCredentials(array $credentials)
    {
        $user = $this
            ->createModel()
            ->findById($credentials['email']);

        if ($user->exists()) {
            return $user;
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required and supported.
     *
     * Required by Illuminate\Contracts\Auth\UserProvider as of Laravel 11.
     * The password lives in the user file's front matter, so a rehash is a
     * set() plus a save() rather than a column update.
     *
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false)
    {
        if (!$this->hasher->needsRehash($user->getAuthPassword()) && !$force) {
            return;
        }

        $user->set('password', $this->hasher->make($credentials['password']))->save();
    }

    /**
     * Create a new instance of the model.
     *
     * @return \AloiaCms\Models\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }
}
