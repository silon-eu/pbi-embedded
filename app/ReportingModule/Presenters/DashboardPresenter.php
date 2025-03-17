<?php

namespace App\ReportingModule\Presenters;

use App\Models\Service\AzureService;
use App\ReportingModule\Controls\DashboardTile;
use Nette\Application\UI\Control;

class DashboardPresenter extends BasePresenter {

    public function __construct(
        protected AzureService $azureService
    )
    {
        parent::__construct();
    }

    protected function createComponentTile(): Control {
        return new DashboardTile;
    }

    public function actionDefault() {
        //$this->template->reportConfig = $this->azureService->getReportConfig('7b38db08-4f79-475b-ae9b-304abbcd9cc5','c78f621e-bc9b-4a15-9cfd-03fa8ca4be19');
    }

    public function actionContracts() {
        //$this->template->reportConfig = $this->azureService->getReportConfig('7b38db08-4f79-475b-ae9b-304abbcd9cc5','c78f621e-bc9b-4a15-9cfd-03fa8ca4be19');
        $this->template->reportConfig = $this->azureService->getReportConfig('d0376018-b3b3-4cdd-abfe-7d32558eddb4','2a619b73-92fc-4c4f-b350-6b116f8e0448');
    }

    public function actionContractsSide() {
        $this->template->reportConfig = $this->azureService->getReportConfig('d0376018-b3b3-4cdd-abfe-7d32558eddb4','2a619b73-92fc-4c4f-b350-6b116f8e0448');
    }
}