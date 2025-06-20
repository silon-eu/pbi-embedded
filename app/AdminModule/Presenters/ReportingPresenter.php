<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Models\Service\RolesService;
use App\AdminModule\Presenters\BasePresenter;
use Ublaboo\DataGrid\DataGrid;

class ReportingPresenter extends BasePresenter {

    public function __construct(
        protected \App\AdminModule\Models\Service\ReportingService $service,
        protected \Nette\Caching\Cache $cache
    )
    {
        parent::__construct();
    }

    /**
     * Component factory
     * @param string $name
     */
    protected function createComponent($name): \Nette\ComponentModel\IComponent {
        switch($name) {
            case 'permissionsDatagrid':
                $permissionsDatagrid = new DataGrid($this,'permissionsDatagrid');
                $permissionsDatagrid->setRememberState(false);
                $permissionsDatagrid->setPrimaryKey('id');
                $permissionsDatagrid->setDataSource($this->service->getPermissions());

                $permissionsDatagrid->addColumnText('tab', 'Tab', 'rep_pages.rep_tiles.rep_tabs.name')
                    ->setSortable()
                    ->setRenderer(function($item) {
                        return $item->rep_pages->rep_tile->rep_tabs->name;
                    })
                    ->setFilterSelect($this->service->getTabs()->order('name')->fetchPairs('name', 'name'),'rep_pages.rep_tiles.rep_tabs.name')
                    ->setPrompt('-- choose --');

                $permissionsDatagrid->addColumnText('tile', 'Tile', 'rep_pages.rep_tiles.name')
                    ->setSortable()
                    ->setRenderer(function($item) {
                        return $item->rep_pages->rep_tile->name;
                    })
                    ->setFilterSelect($this->service->getTiles()->order('name')->fetchPairs('name', 'name'),'rep_pages.rep_tiles.name')
                    ->setPrompt('-- choose --');
                $permissionsDatagrid->addColumnText('page', 'Page', 'rep_pages.name')
                    ->setSortable()
                    ->setFilterText();
                $permissionsDatagrid->addColumnText('group', 'Group', 'groups.name')
                    ->setSortable()
                    ->setFilterText();
                $permissionsDatagrid->addColumnText('group_user', 'Group users')
                    ->setSortable()
                    ->setRenderer(function($item) {
                        if (!$item->groups) {
                            return $item->users->username;
                        } else {
                            $users = [];
                            foreach ($item->groups->related('users.groups_id') as $user) {
                                $users[] = $user->username;
                            }
                            return implode(', ', $users);
                        }
                    });
                $permissionsDatagrid->addColumnText('users', 'Username', 'users.username')
                    ->setSortable()
                    ->setFilterText();

                $permissionsDatagrid->setItemsPerPageList([10, 20, 50, 100, 500]);
                $permissionsDatagrid->setDefaultPerPage(100);
                return $permissionsDatagrid;
            default:
                return parent::createComponent($name);
        }
    }

    public function handleDelete(int $id)
    {
        if ($this->groupsService->deleteGroup($id)) {
            $this->flashMessage('Group has been deleted.','success');
        } else {
            $this->flashMessage('Group could not be deleted.','danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this['groupsDatagrid']->reload();
        } else {
            $this->redirect('this');
        }
    }

    public function renderEdit(?int $id = null) {
        $this->template->group = $id ? $this->groupsService->getGroup($id) : null;
    }
}