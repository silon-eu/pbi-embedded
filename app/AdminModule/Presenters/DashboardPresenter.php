<?php

namespace App\AdminModule\Presenters;

use App\Models\Service\AzureService;
use Tracy\Debugger;

class DashboardPresenter extends BasePresenter {

    public function __construct(
        protected \App\AdminModule\Models\Service\AdminService $service,
        protected AzureService $azureService
    )
    {

    }

    public function actionDefault() {

    }

    public function actionAzureStats() {
        $this->template->availableFeatures = $this->azureService->getAvailableFeatures();
        $this->template->capacities = $this->azureService->getCapacities();
    }
}