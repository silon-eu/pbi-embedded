<?php

namespace App\ReportingModule\Presenters;

use App\Models\Service\AzureService;
use App\ReportingModule\Controls\DashboardTile;
use Nette\Application\UI\Control;
use Tracy\Debugger;

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


    /* FOR PERFORMANCE TEST */

    public function actionCardex() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('e97eb0f2-d671-4b25-b9e6-74b746bc25ec','7cb49801-6f0c-4553-9873-e01bef667d68');
        $this->template->reportName = 'Cardex';
        $this->template->navigation = [
            'ReportSection' => [
                'name' => 'Cardex'
            ],
        ];
    }

    public function actionSalesAcquisitionMatrix() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('c56dd31e-8bd4-4000-9d23-30b6afd73688','45728fce-b33a-4cc8-a821-a283f19a6a92');
        $this->template->reportName = 'Sales acquisition matrix';
        $this->template->navigation = [
            'ReportSection2c70982cc55ae8016339' => [
                'name' => 'Acquisition matrix'
            ],
            '083285a0f5d15f97a25a' => [
                'name' => 'New Accounts by Acq Channel Selected'
            ],
        ];
    }

    public function actionGledger() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('db01467a-b007-48c4-9b36-cdd9f0c5bc3f','51d4f63e-615c-4985-97f0-19d880030c02');
        $this->template->reportName = 'GLedger';
        $this->template->navigation = [
            'c0d4870bc46e65a0cb01' => [
                'name' => 'GLedger'
            ],
        ];
    }

    public function actionCmDb() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('7b38db08-4f79-475b-ae9b-304abbcd9cc5','352e7a67-cf94-4ec8-baa4-ac527b28195a');
        $this->template->reportName = 'CM1 and DB';
        $this->template->navigation = [
            'ReportSection' => [
                'name' => 'CM I.',
                'items' => [
                    'ReportSection' => 'By Account and Customer',
                    'ReportSection428406740c4fe6994095' => 'By Account and Customer 2',
                    'ReportSection1cb834241807d0987041' => 'By Item',
                    'ReportSection03643b85d0b89ad10aa7' => 'By Account and Customer daily',
                    'ReportSectionfa462cadaf667930b088' => 'By Invoice',
                ]
            ],
            'ReportSection4bc5fcb7d44d96dda0bc' => [
                'name' => 'Gross CURRENCY / Kg'
            ],
            'ReportSection9717e0600451d0765aaa' => [
                'name' => 'DB',
                'items' => [
                    'ReportSection9717e0600451d0765aaa' => 'Categories',
                    'ReportSectione560c5c13688b1113b0c' => 'Customers',
                    'ReportSection64eb652441889808a0a8' => 'Detail',
                ]
            ],
            'ReportSectionf3f4772fde4333b8117f' => [
                'name' => 'GM - Continents'
            ],
            'ReportSection6e0169429891287ed018' => [
                'name' => 'Sales Budget'
            ],
        ];
    }

    public function actionSalesForecast() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('7b38db08-4f79-475b-ae9b-304abbcd9cc5','6327062c-67b2-493d-a11b-8922903eb4a9');
        $this->template->reportName = 'Sales Forecast';
        $this->template->navigation = [
            'ReportSection8245d64c3a5514936760' => [
                'name' => 'Forecast',
                'items' => [
                    'ReportSection8245d64c3a5514936760' => 'Overview',
                    'ReportSection2ac918176ad724a74200' => 'By Customer',
                    'ReportSection8ac63a3358c82ab7ac9c' => 'By Product detail',
                    'ReportSectionca8770ea09abc0a55a2f' => 'By Customer detail',
                ]
            ],
            'ReportSection25bff2f0bc50c57ce347' => [
                'name' => 'Comparison Against Volume'
            ],
            'ReportSection8cd2fc9b335bee0d69d3' => [
                'name' => 'Changes',
                'items' => [
                    'ReportSection8cd2fc9b335bee0d69d3' => 'Last 10 Days',
                    'ReportSection42428018c0ce3015257f' => 'Weekly',
                ]
            ],
        ];
    }

    public function actionLineMonitoringUs() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('eabba228-78a6-4cf8-b9b3-115ffbb137f3','5912e5cf-5b31-45f9-bf85-d878f5d9775a');
        $this->template->reportName = 'Line Monitoring 43L001';
        $this->template->navigation = [
            'ReportSectione9d608bf093e522d1d24' => [
                'name' => 'Overview'
            ],
        ];
    }

    public function actionSalesForecastUs() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('eabba228-78a6-4cf8-b9b3-115ffbb137f3','78207182-646c-49fd-9ddd-9b7682dd3e3e');
        $this->template->reportName = 'Sales Forecast US';
        $this->template->navigation = [
            'ReportSection8245d64c3a5514936760' => [
                'name' => 'Forecast',
                'items' => [
                    'ReportSection8245d64c3a5514936760' => 'Overview',
                    'ReportSection2ac918176ad724a74200' => 'By Customer',
                    'ReportSection8ac63a3358c82ab7ac9c' => 'By Product detail',
                    'ReportSectionca8770ea09abc0a55a2f' => 'By Customer detail',
                ]
            ],
            'ReportSection25bff2f0bc50c57ce347' => [
                'name' => 'Comparison Against Volume'
            ],
            'ReportSection8cd2fc9b335bee0d69d3' => [
                'name' => 'Changes (Last 10 Days)'
            ],
        ];
    }

    public function actionSalesDod() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('7b38db08-4f79-475b-ae9b-304abbcd9cc5','be3cac53-78b3-49af-a06e-67fe3c2e033d');
        $this->template->reportName = 'Sales DoD';
        $this->template->navigation = [
            'ReportSectiond21c8598b2e2946c3e84' => [
                'name' => 'Overview'
            ],
        ];
    }

    public function actionSalesDodUs() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('eabba228-78a6-4cf8-b9b3-115ffbb137f3','dd1701c1-a5bd-41fe-b56d-1dafa4c44d59');
        $this->template->reportName = 'Sales DoD US';
        $this->template->navigation = [
            'ReportSectiond21c8598b2e2946c3e84' => [
                'name' => 'Overview'
            ],
        ];
    }

    public function actionProcurement() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('d0376018-b3b3-4cdd-abfe-7d32558eddb4','6777137c-65bb-47c1-8a41-c76752396993');
        $this->template->reportName = 'Procurement';
        $this->template->navigation = [
            '9fc05f1c21e0021ed579' => [
                'name' => 'Overview'
            ],
        ];
    }

    public function actionCardexProcurement() {
        $this->setView('sideNavigation');
        $this->template->reportConfig = $this->azureService->getReportConfig('e4e032b9-037a-4457-8690-3733ecf3918d','0bc72690-1ea1-41da-8e89-b26590ef951b');
        $this->template->reportName = 'Cardex Procurement';
        $this->template->navigation = [
            '8efb87dc03b5cff63452' => [
                'name' => 'Overview'
            ],
        ];
    }

}