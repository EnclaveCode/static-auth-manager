<?php

namespace Enclave\StaticAuthManager\Test;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Enclave\StaticAuthManager\Traits\HasRoles;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{

    use HasRoles;
    use Authorizable;
    use Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'users';
}
