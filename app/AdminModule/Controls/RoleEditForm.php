<?php

namespace App\AdminModule\Controls;

use App\AdminModule\Models\Service\GroupsService;
use App\AdminModule\Models\Service\RolesService;
use App\AdminModule\Models\Service\UsersService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Caching\Cache;
use Nette\ComponentModel\IContainer;

class RoleEditForm extends \Nette\Application\UI\Control {
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
        $form->addText('description', 'Description');

        $groups = $form->addCheckboxList('groups', 'Groups', $this->groupsService->getDatasource()->fetchPairs('id', 'name'));

        $form->addSubmit('submit', 'Save');

        if ($id) {
            $form->setDefaults($this->rolesService->getRole($id)->toArray());
            $groups->setDefaultValue($this->rolesService->getGroups($id)->fetchPairs('groups_id','groups_id'));
        }

        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }

    public function save($form, $values) {
        // update
        if (is_numeric($values->id)) {
            if ($this->rolesService->updateRole(intval($values->id), $values)) {
                $this->getPresenter()->flashMessage('Role updated', 'success');
            } else {
                $this->getPresenter()->flashMessage('Role not updated', 'danger');
            }
            $this->getPresenter()->redirect('this',['id' => $values->id]);
        }
        // create
        else {
            if ($inserted = $this->rolesService->createRole($values)) {
                $this->getPresenter()->flashMessage('Role created', 'success');
                $this->getPresenter()->redirect('this',['id' => $inserted->id]);
            } else {
                $this->getPresenter()->flashMessage('Role not created', 'danger');
            }
        }

        // invalidate permissions cache
        $this->cache->clean([Cache::TAGS => ['sys_authenticator_roles']]);

        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/RoleEditForm.latte');
        $this->template->render();
    }

}