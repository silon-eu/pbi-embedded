<?php

declare(strict_types=1);

namespace App\ReportingModule\Presenters;

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
    }
}
