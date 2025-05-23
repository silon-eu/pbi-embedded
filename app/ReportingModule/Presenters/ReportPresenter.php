<?php

namespace App\ReportingModule\Presenters;

use App\Models\Service\AzureService;
use App\ReportingModule\Models\Service\DashboardService;
use App\ReportingModule\Models\Service\ReportService;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\Attributes\Persistent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Tracy\Debugger;

class ReportPresenter extends BasePresenter {

    #[Persistent]
    public int $id;

    #[Persistent]
    public ?int $activePageId = null;

    public function __construct(
        protected AzureService $azureService,
        protected DashboardService $dashboardService,
        protected ReportService $reportService,
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



        if ($this->getParameter('editPageId')) {

            $row5 = $form->addRow();
            $row5->addCell(10)
                ->addTextArea('filters', 'Filters')
                ->setHtmlAttribute('rows', 5)
                ->setHtmlAttribute('placeholder', 'JSON filters configuration for the page');
            $filterButtonsCell = $row5->addCell(2);
            $filterButtonsCell->addButton('load_page_filters', 'Get filters')
                ->setHtmlAttribute('class', 'btn-sm')
                ->setHtmlAttribute('onclick', 'loadPageFilters(this)');
            $filterButtonsCell->addButton('copy_page_filters', 'Copy filters')
                ->setHtmlAttribute('class', 'btn-sm')
                ->setHtmlAttribute('onclick', 'loadPageFilters(this,"clipboard")');

            $pageData = $this->reportService->getPages()->get($this->getParameter('editPageId'));
            $form->setDefaults([
                'rep_tiles_id' => $pageData->rep_tiles_id,
                'id' => $pageData->id,
                'name' => $pageData->name,
                'description' => $pageData->description,
                'page' => $pageData->page,
                'filters' => $pageData->filters,
            ]);
        }

        $form->addSubmit('send', 'Save');
        $form->onSubmit[] = [$this, 'processPageForm'];

        return $form;
    }

    public function processPageForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = ArrayHash::from($form->getHttpData());
            if ($values->id) {
                $this->reportService->editPage($values);
                $this->flashMessage('Page updated successfully', 'success');
            } else {
                $this->reportService->addPage($values);
                $this->flashMessage('Page added successfully', 'success');
            }
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id);
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

    public function handleShowPageForm(?int $editPageId) {
        $this->allowOnlyRoles(['admin']);

        $this->payload->modalTitle = 'Add or edit page';
        $this->template->systemModalControl = 'pageForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    protected function createComponentReportFilterForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();

        $form->addHidden('id',$this->id);

        $row1 = $form->addRow();
        $row1->addCell(10)
            ->addTextArea('filters', 'Filters')
            ->setDefaultValue($this->dashboardService->getTiles()->get($this->id)->filters)
            ->setHtmlAttribute('rows', 5);
        $filterButtonsCell = $row1->addCell(2);
        $filterButtonsCell->addButton('load_report_filters', 'Get filters')
            ->setHtmlAttribute('class', 'btn-sm')
            ->setHtmlAttribute('onclick', 'loadReportFilters(this)');
        $filterButtonsCell->addButton('copy_page_filters', 'Copy filters')
            ->setHtmlAttribute('class', 'btn-sm')
            ->setHtmlAttribute('onclick', 'loadReportFilters(this,"clipboard")');

        $form->addSubmit('send', 'Save');
        $form->onSubmit[] = [$this, 'processReportFilterForm'];

        return $form;
    }

    public function processReportFilterForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = $form->getValues();
            $this->dashboardService->updateReportFilters($values->id,$values->filters);
            $this->flashMessage('Filters updated successfully', 'success');
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while updating report filters', 'danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->payload->closeModal = true;
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowReportFilterForm() {
        $this->allowOnlyRoles(['admin']);

        $this->payload->modalTitle = 'Report filters';
        $this->template->systemModalControl = 'reportFilterForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeletePage(int $editPageId) {
        $this->allowOnlyRoles(['admin']);

        try {
            $this->reportService->deletePage($editPageId);
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id);
            $this->flashMessage('Page deleted successfully', 'success');
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while deleting the page', 'danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('reportUserMenu');
        } else {
            $this->redirect('this');
        }
    }

    public function handleChangePage(int $activePageId) {
        if ($this->isAjax()) {
            if ($page = $this->reportService->getPages()->get($activePageId)) {
                $this->activePageId = $activePageId;
                $this->template->activePage = $page;
            } else {
                $this->flashMessage('Page not found', 'danger');
                $this->template->activePage = null;
                $this->redrawControl('flashes');
            }
            $this->redrawControl('reportUserMenu');
        } else {
            $this->redirect('this');
        }
    }

    public function handleChangePagePosition(int $editPageId, string $direction) {
        $this->allowOnlyRoles(['admin']);

        try {
            $this->reportService->changePagePosition($editPageId, $direction);
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id);
            $this->flashMessage('Page position changed successfully', 'success');
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while changing the page position', 'danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('reportUserMenu');
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

        if ($this->isAjax()) { // skip it for a faster handlers
            $this->template->reportConfig = null;
        } else {
            $this->template->reportConfig = $this->azureService->getReportConfig($tile->workspace,$tile->report);
        }

        $this->template->tile = $tile;
        $this->template->navigation = $this->reportService->getNavigationForTile($id);

        // if no page is selected, select the first one
        if ($this->activePageId === null && count($this->template->navigation) > 0) {
            $this->activePageId = Arrays::first($this->template->navigation)["id"];
        }

        if ($this->activePageId !== null && $page = $this->reportService->getPages()->get($this->activePageId)) {
            $this->template->activePageData = $page;
            $this->payload->activePageData = $page->toArray();
        } else {
            $this->template->activePageData = null;
        }


        /*[
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