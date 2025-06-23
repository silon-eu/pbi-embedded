<?php

namespace App\AdminModule\Controls;

use Nette\ComponentModel\IContainer;
use \App\AdminModule\Models\Service\GroupsService;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class GroupsDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, GroupsService $groupsService)
    {
        parent::__construct($parent, $name);

        $this->setDataSource($groupsService->getDatasource());
        $this->setAutoSubmit(true);

        $this->addToolbarButton('edit', 'Create')
            ->setIcon('plus')
            ->setTitle('Create')
            ->setClass('btn btn-sm btn-primary');

        $this->addColumnLink('name', 'Name', ':Admin:Groups:edit', params: ['id'])
            ->setSortable('name');

        $this->addAction('edit', 'Edit', 'edit', ['id' => 'id'])
            ->setIcon('pencil')
            ->setClass('btn btn-sm btn-secondary');

        $this->addAction('delete', 'Delete', 'delete!', ['id' => 'id'])
            ->setIcon('trash')
            ->setClass('btn btn-sm btn-danger')
            ->setConfirmation(new StringConfirmation('Do you really want to delete this group?'));


        $this->addFilterText('name', 'Search')
            ->setPlaceholder('Search...');

        $this->setDefaultSort(['name' => 'ASC']);

    }
}