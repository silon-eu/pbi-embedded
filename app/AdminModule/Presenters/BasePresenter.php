<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Ublaboo\DataGrid\DataGrid;

abstract class BasePresenter extends \App\Presenters\BasePresenter {

    protected function createComponent($name): \Nette\ComponentModel\IComponent {
        switch($name) {
            default:
                return parent::createComponent($name);
        }
    }

    public function startup(): void
    {
        parent::startup();
        if (!$this->user->isLoggedIn()) {
            $this->redirect(':Auth:login');
        }
        DataGrid::$iconPrefix = 'bi bi-';
    }
}
