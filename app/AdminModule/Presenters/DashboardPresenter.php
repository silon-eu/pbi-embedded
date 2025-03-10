<?php

namespace App\AdminModule\Presenters;

use Tracy\Debugger;

class DashboardPresenter extends BasePresenter {

    public function __construct(
        protected \App\AdminModule\Models\Service\AdminService $service
    )
    {

    }

    public function actionDefault() {
    }
}