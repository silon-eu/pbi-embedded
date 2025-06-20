<?php

namespace App\ReportingModule\Presenters;

use App\Models\Service\AzureService;
use App\ReportingModule\Models\Service\DashboardService;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class DashboardPresenter extends BasePresenter {

    #[Persistent]
    public ?int $activeTab = null;

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

    /* TABS */

    protected function createComponentTabForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();
        $form->addHidden('id');
        $form->addText('name', 'Name')
            ->setRequired('Please enter a name');

        if ($this->getParameter('editTabId')) {
            $tab = $this->dashboardService->getTabs()->get($this->getParameter('editTabId'));
            $form->setDefaults([
                'id' => $tab->id,
                'name' => $tab->name,
            ]);
        }

        $form->addSubmit('send', 'Save');
        $form->onSuccess[] = [$this, 'processTabForm'];

        return $form;
    }

    public function processTabForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = $form->getValues();
            if ($values->id) {
                $this->dashboardService->editTab($values->id, $values->name);
                $this->flashMessage('Tab updated successfully', 'success');
            } else {
                $this->dashboardService->addTab($values->name);
                $this->flashMessage('Tab added successfully', 'success');
            }
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while saving the tab', 'danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
            $this->payload->closeModal = true;
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowTabsForm(?int $editTabId) {
        $this->allowOnlyRoles(['admin']);

        $this->template->editTabId = $editTabId;
        $this->payload->modalTitle = 'Add or edit tab';
        $this->template->systemModalControl = 'tabForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    public function handleDeleteTab(?int $editTabId) {
        $this->allowOnlyRoles(['admin']);

        if ($editTabId) {
            if ($this->activeTab === $editTabId) {
                $this->activeTab = null;
            }
            $this->dashboardService->deleteTab($editTabId);
            $this->flashMessage('Tab deleted successfully', 'success');
        } else {
            $this->flashMessage('Tab not specified', 'danger');
        }
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
        } else {
            $this->redirect('this');
        }
    }

    public function handleChangeTabPosition(int $editTabId, string $direction) {
        $this->allowOnlyRoles(['admin']);

        if (!in_array($direction, ['increase', 'decrease'])) {
            $this->flashMessage('Invalid direction', 'danger');
        } else {
            try {
                $this->dashboardService->changeTabPosition($editTabId, $direction);
                $this->activeTab = $editTabId;
            } catch (\Exception $e) {
                Debugger::log($e, Debugger::EXCEPTION);
                $this->flashMessage('Error occurred while changing the tab position', 'danger');
            }
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
        } else {
            $this->redirect('this');
        }
    }

    /* TILES */

    protected function createComponentTileForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();
        $tabId = $this->getParameter('editTabId');

        $form->addHidden('id');

        $form->addGroup('General');

        $row1 = $form->addRow();
        $row1->addCell(4)
            ->addSelect('rep_tabs_id', 'Tab', $this->dashboardService->getTabs()->fetchPairs('id', 'name'))
            ->setPrompt('Select tab')
            ->setRequired('Please select a tab')
            ->setDefaultValue($tabId);

        $row2 = $form->addRow();
        $row2->addCell(4)
            ->addText('icon', 'Icon')
            ->setPlaceholder('bar-chart')
            ->setOption('description','Pick some from <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap icons</a>');
        $row2->addCell(8)
            ->addText('name', 'Name')
            ->setRequired('Please enter a name');

        $row3 = $form->addRow();
        $row3->addCell(12)
            ->addTextArea('description', 'Description');

        $form->addGroup('Power BI');

        $row4 = $form->addRow();
        $workspace = $row4->addCell(6)
            ->addSelect('workspace', 'Workspace');
        $workspace->setRequired('Please enter a workspace')
            ->setPrompt('Select workspace')
            ->setItems($this->azureService->getGroups(true));

        $report = $row4->addCell(6)
            ->addSelect('report', 'Report');

        $report->setRequired('Please enter a report')
            ->setPrompt('Select report')
            ->setHtmlAttribute('data-depends', $workspace->getHtmlName())
            ->setHtmlAttribute('data-url', $this->link('Dashboard:FormReports', '#'));

        /*if ($workspace->getValue()) {
            $report->setItems($this->azureService->getReports($workspace->getValue(), true));
        }*/

        /*$form->onAnchor[] = fn() =>
        $report->setItems($workspace->getValue()
            ? $this->azureService->getReports($workspace->getValue())
            : []);*/

        if ($this->getParameter('editTileId')) {
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
        }

        $form->addSubmit('send', 'Save');
        $form->onSubmit[] = [$this, 'processTileForm'];

        return $form;
    }

    public function processTileForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = ArrayHash::from($form->getHttpData());
            $this->activeTab = $values->rep_tabs_id;
            if ($values->id) {
                $this->dashboardService->editTile($values);
                $this->flashMessage('Tile updated successfully', 'success');
            } else {
                $this->dashboardService->addTile($values);
                $this->flashMessage('Tile added successfully', 'success');
            }
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while saving the tile', 'danger');
        }


        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
            $this->payload->closeModal = true;
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowTilesForm(?int $editTileId) {
        $this->allowOnlyRoles(['admin']);

        $this->template->editTileId = $editTileId;
        $this->payload->modalTitle = 'Add or edit tile';
        $this->template->systemModalControl = 'tileForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    protected function createComponentCopyOrMoveTileForm(): Form {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->setAjax();

        $form->addHidden('id');

        $form->addGroup('General');

        $row1 = $form->addRow();
        $row1->addCell(4)
            ->addRadioList('operation', 'Operation', [
            'copy' => 'Copy',
            'move' => 'Move',
        ])->setRequired()
            ->setDefaultValue('copy');

        $row1->addCell(8)
            ->addSelect('rep_tabs_id', 'Target tab', $this->dashboardService->getTabs()->fetchPairs('id', 'name'))
            ->setPrompt('Select tab')
            ->setRequired('Please select a tab');

        $form->addGroup('Copy settings');
        $row2 = $form->addRow();
        $row2->addCell(8)
            ->addText('name', 'Name')
            ->setRequired('Please enter a name');

        $row3 = $form->addRow();
        $row3->addCell(4)
            ->addRadioList('copy_filters', 'Report filters', [
                'yes' => 'Yes',
                'no' => 'No',
            ])->setDefaultValue('yes');

        $row3->addCell(4)
            ->addRadioList('copy_pages', 'Pages', [
                'yes' => 'Yes',
                'no' => 'No',
            ])->setDefaultValue('yes');

        if ($this->getParameter('editTileId')) {
            $tile = $this->dashboardService->getTiles()->get($this->getParameter('editTileId'));
            $form->setDefaults([
                'rep_tabs_id' => $tile->rep_tabs_id,
                'id' => $tile->id,
                'name' => $tile->name.' - Copy',
            ]);
        }

        $form->addSubmit('send', 'Save');
        $form->onSubmit[] = [$this, 'processCopyOrMoveTileForm'];

        return $form;
    }

    public function processCopyOrMoveTileForm(Form $form): void {
        $this->allowOnlyRoles(['admin']);

        try {
            $values = ArrayHash::from($form->getHttpData());
            $this->activeTab = $values->rep_tabs_id;
            $this->dashboardService->copyOrMoveTile($values);
            $this->flashMessage('Tile '.($values->operation === 'copy' ? 'copied' : 'moved').' successfully', 'success');
        } catch (\Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            $this->flashMessage('Error occurred while copying/movíng the tile', 'danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
            $this->payload->closeModal = true;
        } else {
            $this->redirect('this');
        }
    }

    public function handleShowCopyOrMoveTileForm(?int $editTileId) {
        $this->allowOnlyRoles(['admin']);

        $this->template->editTileId = $editTileId;
        $this->payload->modalTitle = 'Copy or move tile';
        $this->template->systemModalControl = 'copyOrMoveTileForm';
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('systemModal');
        } else {
            $this->redirect('this');
        }
    }

    /* This action is used to load reports to tile form */
    public function actionFormReports(string $workspace): void
    {
        $this->allowOnlyRoles(['admin']);
        $reports = $this->azureService->getReports($workspace,true);
        $this->sendJson($reports);
    }

    public function handleDeleteTile(?int $editTileId) {
        $this->allowOnlyRoles(['admin']);

        if ($editTileId) {
            $tile = $this->dashboardService->getTiles()->get($editTileId);
            $this->activeTab = $tile->rep_tabs_id;
            $this->dashboardService->deleteTile($editTileId);
            $this->flashMessage('Tab deleted successfully', 'success');
        } else {
            $this->flashMessage('Tab not specified', 'danger');
        }
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
        } else {
            $this->redirect('this');
        }
    }

    public function handleChangeTilePosition(int $editTileId, string $direction) {
        $this->allowOnlyRoles(['admin']);

        if (!in_array($direction, ['increase', 'decrease'])) {
            $this->flashMessage('Invalid direction', 'danger');
        } else {
            try {
                $tile = $this->dashboardService->getTiles()->get($editTileId);
                $this->activeTab = $tile->rep_tabs_id;
                $this->dashboardService->changeTilePosition($editTileId, $direction);
            } catch (\Exception $e) {
                Debugger::log($e, Debugger::EXCEPTION);
                $this->flashMessage('Error occurred while changing the tab position', 'danger');
            }
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('dashboard');
        } else {
            $this->redirect('this');
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->activeTab = $this->activeTab;
    }

    public function actionDefault() {
        $this->template->tabs = $this->userIsAdmin() ? $this->dashboardService->getTabs() : $this->dashboardService->getUserTabs($this->getUser()->getId());
        $this->template->tiles = $this->userIsAdmin() ? $this->dashboardService->getTilesForAllTabs() : $this->dashboardService->getUserTilesForAllTabs($this->getUser()->getId());
    }
}