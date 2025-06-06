<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Models\Service\RolesService;

class GroupsPresenter extends BasePresenter {

    protected \App\AdminModule\Models\Service\AdminService $service;
    protected \App\AdminModule\Models\Service\GroupsService $groupsService;
    protected RolesService $rolesService;
    protected \Nette\Caching\Cache $cache;

    public function __construct(\App\AdminModule\Models\Service\AdminService $service,
                                \App\AdminModule\Models\Service\GroupsService $groupsService,
                                RolesService $rolesService,
                                \Nette\Caching\Cache $cache)
    {
        parent::__construct();
        $this->service = $service;
        $this->groupsService = $groupsService;
        $this->rolesService = $rolesService;
        $this->cache = $cache;
    }

    /**
     * Component factory
     * @param string $name
     */
    protected function createComponent($name): \Nette\ComponentModel\IComponent {
        switch($name) {
            case 'groupsDatagrid':
                return new \App\AdminModule\Controls\GroupsDatagrid($this, $name, $this->groupsService);
            case 'editForm':
                return new \App\AdminModule\Controls\GroupEditForm($this, $name, $this->groupsService, $this->rolesService, $this->cache);
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