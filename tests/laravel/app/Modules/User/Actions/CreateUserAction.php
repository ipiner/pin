<?php

namespace App\Modules\User\Actions;

use Pin\Action\Action;

class CreateUserAction extends Action
{
    protected function rules()
    {
        return [
            'username' => 'string',
            'realname' => 'string',
            'password' => 'string',
        ];
    }
}
