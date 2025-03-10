<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class AuthPresenter extends BasePresenter
{
    public function renderLogin()
    {

    }

    public function renderLogout()
    {
        $this->getUser()->logout();
    }
}
