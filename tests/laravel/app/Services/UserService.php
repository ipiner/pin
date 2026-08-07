<?php

namespace App\Services;

use Pin\Services\ModelService;
use Pin\Tests\Models\Models\User;

/**
 * @extends ModelService<User>
 */
class UserService extends ModelService
{
    public function __construct()
    {
        parent::__construct(User::class);
    }
}
