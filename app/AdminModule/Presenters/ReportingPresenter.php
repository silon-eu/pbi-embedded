<?php

namespace App\AdminModule\Presenters;

use AdminModule\Controls\AccessLogDatagrid;
use AdminModule\Controls\IconsDatagrid;
use App\AdminModule\Controls\PermissionsDatagrid;
use App\AdminModule\Models\Service\IconsService;
use App\AdminModule\Models\Service\ReportingService;
use App\ReportingModule\Models\Service\AccessLogService;
use Nette\Caching\Cache;

class ReportingPresenter extends BasePresenter {

    public function __construct(
        protected ReportingService $service,
        protected AccessLogService $accessLogService,
        protected IconsService $iconsService,
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
            case 'iconsDatagrid':
                return new IconsDatagrid(
                    parent: $this,
                    name: $name,
                    iconsService: $this->iconsService
                );
            case 'iconsEditForm':
                return new \AdminModule\Controls\IconEditForm(
                    container: $this,
                    name: $name,
                    iconsService: $this->iconsService
                );
            default:
                return parent::createComponent($name);
        }
    }

    public function actionDeleteIcon($id): void
    {
        $this->allowOnlyRoles(['admin']);
        $icon = $this->iconsService->getIconById($id);
        if ($icon) {

            if (!$icon->is_deletable) {
                $this->flashMessage('This icon cannot be deleted, it\'s a default icon', 'danger');
                $this->redirect('icons');
            }

            try {
                $icon->delete();
                unlink(IconsService::ICONS_PATH . $icon->filename);
                $this->flashMessage('Icon deleted', 'success');
            } catch (\Nette\Database\ForeignKeyConstraintViolationException $e) {
                $this->flashMessage('This icon cannot be deleted due to usage at some tile.', 'danger');
            }

        } else {
            $this->flashMessage('Icon not found', 'danger');
        }
        $this->redirect('icons');
    }
}