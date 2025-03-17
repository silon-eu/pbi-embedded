<?php

namespace App\AdminModule\Presenters;

use Nette\Security\SimpleIdentity;

class UsersPresenter extends BasePresenter {

    public function __construct(
        protected \App\AdminModule\Models\Service\AdminService $service,
        protected \App\AdminModule\Models\Service\UsersService $usersService,
        protected \App\AdminModule\Models\Service\GroupsService $groupsService,
        protected \App\AdminModule\Models\Service\RolesService $rolesService,
        protected \Nette\Caching\Cache $cache)
    {

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

    public function handleSignInAsUser(int $id)
    {
        try {
            $row = $this->usersService->getUser($id);
            if (!$row) {
                throw new \Nette\Security\AuthenticationException('Account is not created.');
            } else {
                $identity = new SimpleIdentity(
                    $row->id,
                    $this->authenticator->getRoles($row->id),
                    [
                        'username' => $row->username,
                        'name' => $row->name,
                        'surname' => $row->surname
                    ],
                );
                $this->getUser()->login($identity);
            }
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->flashMessage($e->getMessage(),'danger');
        }

        $this->redirect('Dashboard:');
    }

    public function renderEdit(?int $id = null) {
        $this->template->userData = $id ? $this->usersService->getUser($id) : null;
    }
}