<?php

namespace App\AdminModule\Controls;

use App\AdminModule\Models\Service\GroupsService;
use App\AdminModule\Models\Service\RolesService;
use App\AdminModule\Models\Service\UsersService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Caching\Cache;
use Nette\ComponentModel\IContainer;

class GroupEditForm extends \Nette\Application\UI\Control {
    protected GroupsService $groupsService;

    protected RolesService $rolesService;

    protected Cache $cache;

    public function __construct(?IContainer $container, ?string $name,
                                GroupsService $groupsService,
                                RolesService $rolesService,
                                Cache $cache)
    {
        $this->groupsService = $groupsService;
        $this->rolesService = $rolesService;
        $this->cache = $cache;
    }

    protected function createComponentForm(): BootstrapForm {
        $id = $this->getPresenter()->getParameter('id');
        $form = new BootstrapForm();
        $form->setRenderMode(RenderMode::VERTICAL_MODE);

        $form->addHidden('id', 'ID');
        $form->addText('name', 'Name')->setRequired();

        $roles = $form->addCheckboxList('roles', 'Roles', $this->rolesService->getDatasource()->fetchPairs('id', 'name'));

        $form->addSubmit('submit', 'Save');

        if ($id) {
            $form->setDefaults($this->groupsService->getGroup($id)->toArray());
            $roles->setDefaultValue($this->groupsService->getRoles($id)->fetchPairs('roles_id','roles_id'));
        }

        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }

    public function save($form, $values) {
        // update
        if (is_numeric($values->id)) {
            if ($this->groupsService->updateGroup(intval($values->id), $values)) {
                $this->getPresenter()->flashMessage('Group updated', 'success');
            } else {
                $this->getPresenter()->flashMessage('Group not updated', 'danger');
            }
            $this->getPresenter()->redirect('this',['id' => $values->id]);
        }
        // create
        else {
            if ($inserted = $this->groupsService->createGroup($values)) {
                $this->getPresenter()->flashMessage('Group created', 'success');
                $this->getPresenter()->redirect('this',['id' => $inserted->id]);
            } else {
                $this->getPresenter()->flashMessage('Group not created', 'danger');
            }
        }

        // invalidate permissions cache
        $this->cache->clean([Cache::TAGS => ['sys_authenticator_roles']]);

        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/GroupEditForm.latte');
        $this->template->render();
    }

}