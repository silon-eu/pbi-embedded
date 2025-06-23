<?php

namespace App\AdminModule\Controls;

use Nette\ComponentModel\IContainer;
use \App\AdminModule\Models\Service\UsersService;
use \App\AdminModule\Models\Service\GroupsService;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class UsersDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, UsersService $usersService, GroupsService $groupsService)
    {
        parent::__construct($parent, $name);

        $this->setDataSource($usersService->getDatasource());
        $this->setAutoSubmit(true);

        $this->addToolbarButton('edit', 'Create')
            ->setIcon('plus')
            ->setTitle('Create')
            ->setClass('btn btn-sm btn-primary');


        $this->addColumnLink('username', 'Username',':Admin:Users:edit', params:['id'])
            ->setSortable('username');
        $this->addColumnText('name', 'Name')
            ->setSortable();
        $this->addColumnText('surname', 'Surname')
            ->setSortable();
        $this->addColumnLink('group', 'Group',':Admin:Groups:edit','groups.name',params:['id'=>'groups_id'])
            ->setSortable();

        $this->addAction('this', 'Sign in', 'signInAsUser!', ['id' => 'id'])
            ->setIcon('lock')
            ->setClass('btn btn-sm btn-secondary')
            ->setConfirmation(new StringConfirmation('Do you really want to login as this user?'));

        $this->addAction('edit', 'Edit', 'edit', ['id' => 'id'])
            ->setIcon('pencil')
            ->setClass('btn btn-sm btn-secondary');

        $this->addAction('delete', 'Delete', 'delete!', ['id' => 'id'])
            ->setIcon('trash')
            ->setClass('btn btn-sm btn-danger')
            ->setConfirmation(new StringConfirmation('Do you really want to delete this user?'));


        $this->addFilterSelect('group','Group',$groupsService->getKeyPairs(),'groups_id')
            ->setPrompt('All');
        $this->addFilterText('username', 'Search:', ['username', 'name', 'surname'])
            ->setPlaceholder('Search...');

        $this->setDefaultSort(['username' => 'ASC']);

    }
}