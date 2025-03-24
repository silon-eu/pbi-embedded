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

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->activeTab = $this->getHttpRequest()->getUrl()->getQueryParameter('activeTab');
    }

    public function actionDefault() {

    }

    public function actionContracts() {
        $this->setView('topNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('d0376018-b3b3-4cdd-abfe-7d32558eddb4','2a619b73-92fc-4c4f-b350-6b116f8e0448');
    }

    public function actionContractsSide() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('d0376018-b3b3-4cdd-abfe-7d32558eddb4','2a619b73-92fc-4c4f-b350-6b116f8e0448');
        $this->template->reportName = 'Contracts';
        $this->template->navigation = [
            'ReportSectioncce5b109d49fdbf042c3' => [
                'name' => 'Contracts by Categories',
                'items' => [
                    'ReportSectioncce5b109d49fdbf042c3' => 'All Sales and Credit',
                    'ced1608670a739039568' => 'Green Invoices',
                    '7fd37f20db9de97abcba' => 'Credit Notes',
                    '11e07b900880c0750790' => 'Quality',
                    'a4a4db5030a10287b8c8' => 'Breakdown by SEG4',
                ]
            ],
            '34d63374b0d45b154e43' => [
                'name' => 'Contracts by Customers'
            ],
            '36518aa9bc041582c699' => [
                'name' => 'Contracts by Category and Customers'
            ],
            '9f8b8fce0de4d389d4d4' => [
                'name' => 'Yield on waste'
            ],
        ];
    }

    public function actionProfitability() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('d0376018-b3b3-4cdd-abfe-7d32558eddb4','e93122d8-1a51-47c2-8654-c868ac516360');
        $this->template->reportName = 'Profitability';
        $this->template->navigation = [
            'be1c17c97390691bc054' => [
                'name' => 'Budget Fulfillment CZ'
            ],
            '2b49c81fbb1862bdd533' => [
                'name' => 'Budget Fulfillment US'
            ],
            '1d26676fbe10fcde80ea' => [
                'name' => 'Forecast'
            ],
        ];
    }
}