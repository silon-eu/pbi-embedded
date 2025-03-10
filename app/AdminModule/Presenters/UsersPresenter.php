<?php

namespace App\AdminModule\Presenters;

class UsersPresenter extends BasePresenter {

    protected \App\AdminModule\Models\Service\AdminService $service;
    protected \App\AdminModule\Models\Service\UsersService $usersService;
    protected \App\AdminModule\Models\Service\GroupsService $groupsService;
    protected \Nette\Caching\Cache $cache;

    public function __construct(\App\AdminModule\Models\Service\AdminService $service,
                                \App\AdminModule\Models\Service\UsersService $usersService,
                                \App\AdminModule\Models\Service\GroupsService $groupsService,
                                \Nette\Caching\Cache $cache)
    {
        $this->service = $service;
        $this->usersService = $usersService;
        $this->groupsService = $groupsService;
        $this->cache = $cache;
    }

    /**
     * Component factory
     * @param string $name
     */
    protected function createComponent($name): \Nette\ComponentModel\IComponent {
        switch($name) {
            case 'usersDatagrid':
                return new \App\AdminModule\Controls\UsersDatagrid($this, $name, $this->usersService, $this->groupsService);
            case 'editForm':
                return new \App\AdminModule\Controls\UserEditForm($this, $name, $this->usersService, $this->groupsService, $this->cache);
            default:
                return parent::createComponent($name);
        }
    }

    public function handleDelete(int $id)
    {
        if ($this->usersService->deleteUser($id)) {
            $this->flashMessage('User has been deleted.','success');
        } else {
            $this->flashMessage('User could not be deleted.','danger');
        }

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this['usersDatagrid']->reload();
        } else {
            $this->redirect('this');
        }
    }

    public function renderEdit(?int $id = null) {
        $this->template->userData = $id ? $this->usersService->getUser($id) : null;
    }
}