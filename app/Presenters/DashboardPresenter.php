<?php

//declare(strict_types=1);

namespace App\Presenters;

use Ublaboo\DataGrid\DataGrid;
use GuzzleHttp\Exception\ClientException;

final class DashboardPresenter extends BasePresenter {


    public function __construct() {
        parent::__construct();
    }

    public function actionDefault() {
        $this->forward('Reporting:Dashboard:default');
    }
}
