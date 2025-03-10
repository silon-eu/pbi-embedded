<?php

namespace App\AdminModule\Controls;

use App\AdminModule\Models\Service\RolesService;
use Nette\ComponentModel\IContainer;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class RolesDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, RolesService $rolesService)
    {
        parent::__construct($parent, $name);

        $this->setDataSource($rolesService->getDatasource());
        $this->setAutoSubmit(true);

        $this->addToolbarButton('edit', 'Create')
            ->setIcon('plus')
            ->setTitle('Create')
            ->setClass('btn btn-sm btn-primary');

        $this->addColumnText('name', 'Name');

        $this->addColumnText('description', 'Description');

        $this->addAction('edit', 'Edit', 'edit', ['id' => 'id'])
            ->setIcon('pencil')
            ->setClass('btn btn-sm btn-secondary');

        $this->addAction('delete', 'Delete', 'delete!', ['id' => 'id'])
            ->setIcon('trash')
            ->setClass('btn btn-sm btn-danger')
            ->setConfirmation(new StringConfirmation('Do you really want to delete this role?'));


        $this->addFilterText('name', 'Search')
            ->setPlaceholder('Search...');

        $this->setDefaultSort(['name' => 'ASC']);

    }
}