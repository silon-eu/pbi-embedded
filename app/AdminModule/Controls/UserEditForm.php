<?php

namespace App\AdminModule\Controls;

use App\AdminModule\Models\Service\GroupsService;
use App\AdminModule\Models\Service\UsersService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Caching\Cache;
use Nette\ComponentModel\IContainer;

class UserEditForm extends \Nette\Application\UI\Control {

    protected UsersService $usersService;
    protected GroupsService $groupsService;

    protected Cache $cache;

    public function __construct(?IContainer $container, ?string $name,
                                UsersService $usersService, GroupsService $groupsService,
                                Cache $cache)
    {
        $this->usersService = $usersService;
        $this->groupsService = $groupsService;
        $this->cache = $cache;
    }

    protected function createComponentForm(): BootstrapForm {
        $form = new BootstrapForm();
        $form->setRenderMode(RenderMode::VERTICAL_MODE);

        $form->addHidden('id', 'ID');
        $form->addText('username', 'Username')->setRequired();
        $form->addText('name', 'Name');
        $form->addText('surname', 'Surname');
        $form->addSelect('groups_id', 'Group', $this->groupsService->getKeyPairs())->setRequired()->setPrompt('Select group')
            ->getControlPrototype()
            ->appendAttribute('class', 'selectpicker')
            ->appendAttribute('data-live-search', 'true');

        $form->addSubmit('submit', 'Save');

        if ($this->getPresenter()->getParameter('id')) {
            $form->setDefaults($this->usersService->getUser($this->getPresenter()->getParameter('id'))->toArray());
        }

        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }

    public function save($form, $values) {
        // update
        if (is_numeric($values->id)) {
            if ($this->usersService->updateUser(intval($values->id), $values)) {
                $this->getPresenter()->flashMessage('User updated', 'success');
                $this->cache->remove('sys_authenticator_roles_'.$values->id);
            } else {
                $this->getPresenter()->flashMessage('User not updated', 'danger');
            }
            $this->getPresenter()->redirect('this',['id' => $values->id]);
        }
        // create
        else {
            if ($inserted = $this->usersService->createUser($values)) {
                $this->getPresenter()->flashMessage('User created', 'success');
                $this->getPresenter()->redirect('this',['id' => $inserted->id]);
            } else {
                $this->getPresenter()->flashMessage('User not created', 'danger');
            }
        }

        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/UserEditForm.latte');
        $this->template->render();
    }

}