<?php

namespace App\ReportingModule\Presenters;

use App\Models\Service\AzureService;
use App\ReportingModule\Models\Service\DashboardService;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\Attributes\Persistent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class ReportPresenter extends BasePresenter {

    #[Persistent]
    public int $id;

    public function __construct(
        protected AzureService $azureService,
        protected DashboardService $dashboardService
    )
    {
        parent::__construct();
    }

    public function startup(): void {
        parent::startup();
    }
    public function beforeRender(): void
    {
        parent::beforeRender();
    }

    protected function createComponentPageForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();

        $form->addHidden('id');
        $form->addHidden('rep_tiles_id', $this->id);

        $form->addGroup('General');


        $row1 = $form->addRow();
        $row1->addCell(4)
            ->addText('name', 'Name')
            ->setRequired('Please enter a name');

        $row2 = $form->addRow();
        $row2->addCell(12)
            ->addTextArea('description', 'Description');

        $form->addGroup('Power BI');

        $row4 = $form->addRow();
        $page = $row4->addCell(6)
            ->addSelect('page', 'Report page');

        $tile = $this->dashboardService->getTiles()->get($this->id);
        $page->setRequired('Please select a report page')
            ->setPrompt('Select page')
            ->setItems($this->azureService->getPages($tile->workspace, $tile->report, true));

        /*if ($this->getParameter('editTileId')) {
            $tile = $this->dashboardService->getTiles()->get($this->getParameter('editTileId'));
            $report->setItems($this->azureService->getReports($tile->workspace, true));
            $form->setDefaults([
                'rep_tabs_id' => $tile->rep_tabs_id,
                'id' => $tile->id,
                'name' => $tile->name,
                'description' => $tile->description,
                'icon' => $tile->icon,
                'workspace' => $tile->workspace,
                'report' => $tile->report,
            ]);
        }*/

        $form->addSubmit('send', 'Save');
        $form->onSubmit[] = [$this, 'processPageForm'];

        return $form;
    }

    public function processPageForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = ArrayHash::from($form->getHttpData());
            /*$this->activeTab = $values->rep_tabs_id;
            if ($values->id) {
                $this->dashboardService->editTile($values);
                $this->flashMessage('Tile updated successfully', 'success');
            } else {
                $this->dashboardService->addTile($values);
                $this->flashMessage('Tile added successfully', 'success');
            }*/
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while saving the page', 'danger');
        }


        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('reportUserMenu');
            $this->payload->closeModal = true;
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowPageForm(?int $pageId) {
        $this->allowOnlyRoles(['admin']);

        $this->payload->modalTitle = 'Add or edit page';
        $this->template->systemModalControl = 'pageForm';
        if ($this->isAjax()) {
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    public function actionDetail(int $id) {
        $tile = $this->dashboardService->getTiles()->get($id);
        if (!$tile) {
            throw new BadRequestException('Report not found');
        }

        $this->setView('sideNavigation');

        $this->template->reportConfig = $this->azureService->getReportConfig($tile->workspace,$tile->report);
        $this->template->tile = $tile;
        $this->template->navigation = [];/*[
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
        ];*/

        $this->template->adminNavigation = $this->azureService->getPages($tile->workspace, $tile->report);
    }

}