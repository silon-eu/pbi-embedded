<?php

namespace App\AdminModule\Presenters;

use AdminModule\Controls\AccessLogDatagrid;
use App\AdminModule\Controls\PermissionsDatagrid;
use App\AdminModule\Models\Service\ReportingService;
use App\ReportingModule\Models\Service\AccessLogService;
use Nette\Caching\Cache;

class ReportingPresenter extends BasePresenter {

    public function __construct(
        protected ReportingService $service,
        protected AccessLogService $accessLogService,
        protected Cache $cache
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
            case 'accessLogDatagrid':
                return new AccessLogDatagrid(
                    parent: $this,
                    name: $name,
                    reportingService: $this->service,
                    accessLogService: $this->accessLogService
                );
            default:
                return parent::createComponent($name);
        }
    }
}