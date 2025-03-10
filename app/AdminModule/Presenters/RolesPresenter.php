<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Models\Service\RolesService;

class RolesPresenter extends BasePresenter {

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
            case 'rolesDatagrid':
                return new \App\AdminModule\Controls\RolesDatagrid($this, $name, $this->rolesService);
            case 'editForm':
                return new \App\AdminModule\Controls\RoleEditForm($this, $name, $this->groupsService, $this->rolesService, $this->cache);
            default:
                return parent::createComponent($name);
        }
    }

    public function handleDelete(int $id)
    {
        if ($this->rolesService->deleteRole($id)) {
            $this->flashMessage('Role has been deleted.','success');
        } else {
            $this->flashMessage('Role could not be deleted.','danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this['rolesDatagrid']->reload();
        } else {
            $this->redirect('this');
        }
    }

    public function renderEdit(?int $id = null) {
        $this->template->role = $id ? $this->rolesService->getRole($id) : null;
    }
}