<?php

namespace App\ReportingModule\Presenters;

use App\AdminModule\Models\Service\GroupsService;
use App\AdminModule\Models\Service\UsersService;
use App\Models\Service\AzureService;
use App\ReportingModule\Models\Service\AccessLogService;
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
        protected UsersService $usersService,
        protected GroupsService $groupsService,
        protected AccessLogService $accessLogService,
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

        $this->template->filterSubstitutions = $this->getFilterSubstitutions();

        $this->template->addFilter('applyDynamicProperties', function (?string $json): ?string {
            return $this->applyDynamicProperties($json);
        });
    }

    protected function createComponentPageForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();

        $form->addHidden('id');
        $form->addHidden('rep_tiles_id', $this->id);

        $form->addGroup('General');


        $row1 = $form->addRow();
        $row1->addCell(6)
            ->addText('name', 'Name')
            ->setRequired('Please enter a name');

        $row2 = $form->addRow();
        $row2->addCell(6)
            ->addTextArea('description', 'Description')
            ->getControlPrototype()->appendAttribute('class','rich-text-editor');

        $row2->addCell(6)
            ->addText('desc_link', 'Wiki link');

        $form->addGroup('Power BI');

        $row4 = $form->addRow();
        $page = $row4->addCell(6)
            ->addSelect('page', 'Report page')
            ->checkDefaultValue(false);

        $tile = $this->dashboardService->getTiles()->get($this->id);
        $pagesList = $this->azureService->getPages($tile->workspace, $tile->report, true);
        $page->setRequired('Please select a report page')
            ->setPrompt('Select page')
            ->setItems($pagesList);



        if ($this->getParameter('editPageId')) {

            $row5 = $form->addRow();

            $row5->addCell(6)
                ->addTextArea('slicers', 'Visual slicers')
                ->setHtmlAttribute('rows', 25)
                ->setHtmlAttribute('placeholder', 'JSON slicers configuration for visuals on this page')
                ->setHtmlAttribute('class','codemirror-json');

            $row5->addCell(5)
                ->addTextArea('filters', 'Page level filters')
                ->setHtmlAttribute('rows', 25)
                ->setHtmlAttribute('placeholder', 'JSON filters configuration for the page')
                ->setHtmlAttribute('class','codemirror-json');
            $filterButtonsCell = $row5->addCell(1);
            $filterButtonsCell->getElementPrototype()->appendAttribute('class','pt-1');
            $filterButtonsCell->addButton('load_page_filters', '<i class="bi bi-input-cursor-text"></i>')
                ->setHtmlAttribute('class', 'btn-sm mt-4')
                ->setHtmlAttribute('onclick', 'loadPageFilters(this)')
                ->setHtmlAttribute('data-bs-toggle', 'tooltip')
                ->setHtmlAttribute('data-bs-placement', 'right')
                ->setHtmlAttribute('data-bs-title', 'Load filters that are currently set on page into the field');

            $filterButtonsCell->addButton('copy_page_filters', '<i class="bi bi-copy"></i>')
                ->setHtmlAttribute('class', 'btn-sm')
                ->setHtmlAttribute('onclick', 'loadPageFilters(this,"clipboard")')
                ->setHtmlAttribute('data-bs-toggle', 'tooltip')
                ->setHtmlAttribute('data-bs-placement', 'right')
                ->setHtmlAttribute('data-bs-title', 'Copy filters that are currently set on page to clipboard');

            $form->addGroup('Permissions');

            $row6 = $form->addRow();
            $row6->addCell(6)
                ->addMultiSelect('group_permissions', 'Groups', $this->groupsService->getDatasource()->order('name')->fetchPairs('id', 'name'))
                ->setHtmlAttribute('class', 'dual-listbox');
            $row6->addCell(6)
                ->addMultiSelect('user_permissions', 'Users',$this->usersService->getFullNameListForSelect())
                ->setHtmlAttribute('class', 'dual-listbox');

            $pageData = $this->reportService->getPages()->get($this->getParameter('editPageId'));
            $form->setDefaults([
                'rep_tiles_id' => $pageData->rep_tiles_id,
                'id' => $pageData->id,
                'name' => $pageData->name,
                'description' => $pageData->description,
                'page' => $pageData->page,
                'filters' => $pageData->filters,
                'slicers' => $pageData->slicers,
                'group_permissions' => $this->reportService->getGroupPermissionsForPage($pageData->id)->fetchPairs('groups_id', 'groups_id'),
                'user_permissions' => $this->reportService->getUserPermissionsForPage($pageData->id)->fetchPairs('users_id', 'users_id'),
            ]);

            if (!array_key_exists($pageData->page, $pagesList)) {
                $page->addError('Currently selected page is not available.');
            }

        }

        //$form->addSubmit('send', 'Save');
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
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id, $this->getUser()->getId(), $this->userIsAdmin());
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
        $this->template->systemModalSize = 'xl';
        $this->template->systemModalControl = 'pageForm';
        $this->template->systemModalFormId = 'frm-pageForm';
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
        $row1->addCell(11)
            ->addTextArea('filters', 'Report level filters')
            ->setDefaultValue($this->dashboardService->getTiles()->get($this->id)->filters)
            ->setHtmlAttribute('rows', 25)
            ->setHtmlAttribute('class','codemirror-json');

        $filterButtonsCell = $row1->addCell(1);
        $filterButtonsCell->getElementPrototype()->appendAttribute('class','pt-1');
        $filterButtonsCell->addButton('load_report_filters', '<i class="bi bi-input-cursor-text"></i>')
            ->setHtmlAttribute('class', 'btn-sm mt-4')
            ->setHtmlAttribute('onclick', 'loadReportFilters(this)')
            ->setHtmlAttribute('data-bs-toggle', 'tooltip')
            ->setHtmlAttribute('data-bs-placement', 'right')
            ->setHtmlAttribute('data-bs-title', 'Load filters that are currently set on report into the field');

        $filterButtonsCell->addButton('copy_page_filters', '<i class="bi bi-copy"></i>')
            ->setHtmlAttribute('class', 'btn-sm')
            ->setHtmlAttribute('onclick', 'loadReportFilters(this,"clipboard")')
            ->setHtmlAttribute('data-bs-toggle', 'tooltip')
            ->setHtmlAttribute('data-bs-placement', 'right')
            ->setHtmlAttribute('data-bs-title', 'Copy filters that are currently set on report to clipboard');

        //$form->addSubmit('send', 'Save');
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
        $this->template->systemModalSize = 'xl';
        $this->template->systemModalControl = 'reportFilterForm';
        $this->template->systemModalFormId = 'frm-reportFilterForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    protected function createComponentCopyOrMovePageForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();

        $pageId = $this->getParameter('editPageId');
        $form->addHidden('id',$pageId);

        $tile = $this->dashboardService->getTiles()->get($this->id);
        $page = $pageId ? $this->reportService->getPages()->get($this->getParameter('editPageId')) : false;

        $general = $form->addRow();
        $general->addCell(4)->addRadioList('operation', 'Operation', [
                'copy' => 'Copy',
                'move' => 'Move',
            ])->setRequired()
            ->setDefaultValue('copy');

        $general->addCell(8)->addSelect('target_tile', 'Target tile', $this->dashboardService->getSimilarTiles($tile->id))
            ->setDefaultValue($tile->id);

        $form->addGroup('Copy settings');
        $copySettings = $form->addRow();

        $copySettings->addCell(12)->addText('name', 'Page name')
            ->setRequired('Please enter a name for the new page')
            ->setDefaultValue(($page ? $page->name : 'not-found') . ' - Copy');

        $copySettings2 = $form->addRow();

        $copySettings2->addCell(4)->addRadioList('copy_filters', 'Copy filters', [
            'yes' => 'Yes',
            'no' => 'No',
        ])->setRequired()
        ->setDefaultValue('yes');

        $copySettings2->addCell(4)->addRadioList('copy_slicers', 'Copy slicers', [
            'yes' => 'Yes',
            'no' => 'No',
        ])->setRequired()
        ->setDefaultValue('yes');

        $copySettings2->addCell(4)->addRadioList('copy_permissions', 'Copy permissions', [
            'yes' => 'Yes',
            'no' => 'No',
        ])->setRequired()
        ->setDefaultValue('no');

        /*$form->addRow()->addCell(12)
            ->addSubmit('send', 'Save');*/
        $form->onSubmit[] = [$this, 'processCopyOrMovePageForm'];

        return $form;
    }

    public function processCopyOrMovePageForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = $form->getValues();
            $this->reportService->copyOrMovePage($values);
            $this->flashMessage('Page '.($values->operation === 'copy' ? 'copied' : 'moved').' successfully', 'success');
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id, $this->getUser()->getId(), $this->userIsAdmin());
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while copying/movíng page', 'danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('reportUserMenu');
            $this->payload->closeModal = true;
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowCopyOrMovePageForm(int $editPageId) {
        $this->allowOnlyRoles(['admin']);

        $this->payload->modalTitle = 'Copy or move page';
        //$this->template->systemModalSize = 'xl';
        $this->template->systemModalControl = 'copyOrMovePageForm';
        $this->template->systemModalFormId = 'frm-copyOrMovePageForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowPageDescription() {
        if ($page = $this->reportService->getPages()->wherePrimary($this->activePageId)->fetch()) {
            $this->payload->modalTitle = $page->name;
            $this->payload->modalBody = $page->description;
        }
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
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id, $this->getUser()->getId(), $this->userIsAdmin());
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
                $this->template->activePageData = (object) $page->toArray();
                $this->template->activePageData->slicers = $this->applyDynamicProperties($this->template->activePageData->slicers);
            } else {
                $this->flashMessage('Page not found', 'danger');
                $this->template->activePageData = null;
                $this->redrawControl('flashes');
            }
            $this->redrawControl('reportUserMenu');

            $this->redrawControl('pageToolbarButtons');
        } else {
            $this->redirect('this');
        }
    }

    public function handleResetFilters(int $activePageId) {
        if ($this->isAjax()) {
            if ($page = $this->reportService->getPages()->get($activePageId)) {
                $this->activePageId = $activePageId;
                $this->payload->resetFilters = true;
                $this->template->activePageData = (object) $page->toArray();
                $this->template->activePageData->slicers = $this->applyDynamicProperties($this->template->activePageData->slicers);
            } else {
                $this->flashMessage('Page not found', 'danger');
                $this->template->activePageData = null;
                $this->redrawControl('flashes');
            }
            $this->redrawControl('reportUserMenu');
            $this->redrawControl('pageToolbarButtons');
        } else {
            $this->redirect('this');
        }
    }

    public function handleChangePagePosition(int $editPageId, string $direction) {
        $this->allowOnlyRoles(['admin']);

        try {
            $this->reportService->changePagePosition($editPageId, $direction);
            $this->template->navigation = $this->reportService->getNavigationForTile($this->id, $this->getUser()->getId(), $this->userIsAdmin());
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
        $this->template->tile = $tile;
        $this->setView('sideNavigation');

        $this->template->navigation = $this->reportService->getNavigationForTile($id, $this->getUser()->getId(), $this->userIsAdmin());

        // if no page is selected, select the first one
        if ($this->activePageId === null && count($this->template->navigation) > 0) {
            $this->forward('this', ['id' => $id, 'activePageId' => Arrays::first($this->template->navigation)["id"]]);
        }

        if ($this->isAjax()) { // skip it for a faster handlers
            $this->template->reportConfig = null;
        } else {
            try {
                $this->template->reportConfig = $this->azureService->getReportConfig($tile->workspace,$tile->report);
            } catch (\Exception $e) {
                //Debugger::log($e, Debugger::EXCEPTION);
                $this->template->exception = $e;
                $this->setView('loadingError');
                return;
            }
        }

        $this->template->refreshDates = $this->reportService->getRefreshDates(strval($tile->rep_refresh_date_ident));

        // if user is not admin and has no navigation items, throw an error
        if (!$this->userIsAdmin() && count($this->template->navigation) === 0) {
            throw new BadRequestException('You do not have access to this report');
        }

        // if user is not admin and navigation does not contain the activePageId in subarray with id key, throw an error
        if (!$this->userIsAdmin() && $this->activePageId !== null && !Arrays::some($this->template->navigation, fn($item) => $item['id'] === $this->activePageId)) {
            throw new BadRequestException('You do not have access to this page');
        }

        if ($this->activePageId !== null && $page = $this->reportService->getPages()->get($this->activePageId)) {
            $this->template->activePageData = $page;
            if ($this->isAjax()) {
                $this->payload->activePageData = $page->toArray();
                $this->payload->activePageData['slicers'] = $this->applyDynamicProperties($page->slicers);
            }
        } else {
            $this->template->activePageData = null;
        }
        $this->accessLogService->logAccess(tabId: $tile->rep_tabs_id, tileId: $tile->id, pageId: $this->activePageId, userId: $this->getUser()->getId());

        if ($this->userIsAdmin()) {
            $this->template->adminNavigation = $this->template->reportConfig ? $this->azureService->getPages($tile->workspace, $tile->report) : null;
        }
    }

    public function getFilterSubstitutions(): array
    {
        return [
            '%DATE.CURRENT.YEAR%' => date('Y'),
            '%DATE.CURRENT.MONTH%' => date('n'),
            '%DATE.CURRENT.MONTH.LEADING_ZERO%' => date('m'),
            '%DATE.CURRENT.MONTH.SHORT%' => date('M'),
            '%DATE.CURRENT.MONTH.LONG%' => date('F'),
            '%DATE.CURRENT.DAY%' => date('j'),
            '%DATE.CURRENT.DAY.LEADING_ZERO%' => date('d'),
            '%DATE.PREVIOUS.YEAR%' => date('Y', strtotime('previous year')),
            '%DATE.PREVIOUS.MONTH%' => date('n', strtotime('previous month')),
            '%DATE.PREVIOUS.MONTH.LEADING_ZERO%' => date('m', strtotime('previous month')),
            '%DATE.PREVIOUS.MONTH.SHORT%' => date('M', strtotime('previous month')),
            '%DATE.PREVIOUS.MONTH.LONG%' => date('F', strtotime('previous month')),
            '%DATE.PREVIOUS.DAY%' => date('j', strtotime('previous day')),
            '%DATE.PREVIOUS.DAY.LEADING_ZERO%' => date('d', strtotime('previous day')),
            '%USER.USERNAME%' => $this->getUser()->getIdentity()->getData()['username'] ?? '',
            '%USER.NAME%' => $this->getUser()->getIdentity()->getData()['name'] ?? '',
            '%USER.SURNAME%' => $this->getUser()->getIdentity()->getData()['surname'] ?? '',
        ];
    }

    public function applyDynamicProperties(?string $json): ?string
    {
        if (empty($json)) {
            return null;
        }
        return str_replace(array_keys($this->getFilterSubstitutions()), array_values($this->getFilterSubstitutions()), $json);
    }

}