<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Controls\PermissionsDatagrid;
use App\AdminModule\Models\Service\RolesService;
use App\AdminModule\Presenters\BasePresenter;
use Ublaboo\DataGrid\DataGrid;

class ReportingPresenter extends BasePresenter {

    public function __construct(
        protected \App\AdminModule\Models\Service\ReportingService $service,
        protected \Nette\Caching\Cache $cache
    )
    {
        parent::__construct();
    }

    /**
     * Component factory
     * @param string $name
     */
    protected function createComponent($name): \Nette\ComponentModel\IComponent {
        switch($name) {
            case 'permissionsDatagrid':
                return new PermissionsDatagrid(
                    parent: $this,
                    name: $name,
                    reportingService: $this->service
                );
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