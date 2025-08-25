<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class AuthPresenter extends BasePresenter
{
    public function renderLogin()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(':Reporting:Dashboard:default');
        }
    }

    public function renderLogout()
    {
        $this->getUser()->logout();
    }
}
