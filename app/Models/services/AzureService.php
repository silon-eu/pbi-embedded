<?php
namespace App\Models\Service;

use App\Models\BaseService;
use DateTimeZone;
use Nette\Caching\Cache;
use Nette\Utils\Json;
use Tracy\Debugger;

class AzureService extends BaseService
{
    protected ?object $accessToken = null;

    const string IN_CAPACITY = 'In capacity';
    const string OUT_OF_CAPACITY = 'Out of capacity';

    public function __construct(
        protected array $config,
        protected Cache $cache
    ){
        $this->accessToken = $this->getToken();
    }

    protected function getToken(): ?object
    {
        return $this->cache->load('azureAuthToken', function (&$dependencies) {

            $guzzle = new \GuzzleHttp\Client();
            $url = $this->config['authorityUrl'] . $this->config['tenantId'] . '/oauth2/v2.0/token';
            $token = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => $this->config['clientId'],
                    'client_secret' => $this->config['clientSecret'],
                    'scope' => $this->config['scopeBase'],
                    'grant_type' => 'client_credentials',
                ],
            ])->getBody()->getContents());

            $dependencies[Cache::Expire] = $token->expires_in - 2 . ' seconds';

            return $token;
        });
    }

    public function getReportConfig(string $workspaceId, string $reportId): ?object
    {
        return $this->cache->load('reportConfig_'.$workspaceId.'_'.$reportId, function (& $dependencies) use ($workspaceId, $reportId) {
            $dependencies[Cache::Expire] = '5 seconds';

            $embedParams = $this->getEmbedParamsForSingleReport($workspaceId, $reportId);
            $embedToken = $this->getEmbedTokenForSingleReportSingleWorkspace($reportId, [$embedParams->datasetId], $workspaceId);

            $embedParams->embedToken = $embedToken->token;
            $embedParams->expiration = $embedToken->expiration;
            $embedParams->datasetLastRefreshDate = $this->getDatasetLastRefreshDate($embedParams->datasetId, $embedParams->datasetWorkspaceId);

            return $embedParams;
        });
    }

    public function getAvailableFeatures(): ?object
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/availableFeatures';
        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
            //Debugger::barDump
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        return $data;
    }

    public function getCapacities(): ?object
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/capacities';
        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
            //Debugger::barDump
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        return $data;
    }

    protected function getEmbedParamsForSingleReport(string $workspaceId, string $reportId): ?object
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/groups/' . $workspaceId . '/reports/' . $reportId;
        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
            //Debugger::barDump($data,'embedToken');
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        return $data;
    }

    protected function getEmbedTokenForSingleReportSingleWorkspace(string $reportId, array $datasetIds, string $targetWorkspaceId): ?object
    {
        $formData = [
            'reports' => [
                [
                    'id' => $reportId,
                ],
            ],
            'datasets' => [],
        ];

        foreach ($datasetIds as $datasetId) {
            $formData['datasets'][] = [
                'id' => $datasetId,
            ];
        }

        if ($targetWorkspaceId) {
            $formData['targetWorkspaces'] = [
                [
                    'id' => $targetWorkspaceId,
                ],
            ];
        }

        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/GenerateToken';
        $headers = $this->getRequestHeader();

        try {
            $result = $guzzle->post($url, [
                'headers' => $headers,
                'json' => $formData,
            ]);

            $data = Json::decode($result->getBody()->getContents());
            //Debugger::barDump($data,'embedToken');
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump(Json::decode($e->getResponse()->getBody()->getContents()),'error');
        }

        return $data;
    }

    public function getDatasetRefreshes(string $datasetId, ?string $datasetWorkspaceId = null): ?object
    {
        $guzzle = new \GuzzleHttp\Client();
        if ($datasetWorkspaceId) {
            $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/groups/'. $datasetWorkspaceId . '/datasets/'. $datasetId . '/refreshes';
        } else {
            $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/datasets/'. $datasetId . '/refreshes';
        }

        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        return $data;
    }

    public function getDatasetLastRefreshDate(string $datasetId, ?string $datasetWorkspaceId = null): string|\DateTime
    {
        return $this->cache->load('datasetLastRefresh'.$datasetId.'_'.$datasetWorkspaceId, function (&$dependencies) use ($datasetId, $datasetWorkspaceId) {
            $dependencies[Cache::Expire] = '5 minutes';

            $refreshes = $this->getDatasetRefreshes($datasetId, $datasetWorkspaceId);
            if (isset($refreshes->value)) {
                if (count($refreshes->value) > 0) {
                    // get first completed refresh
                    foreach ($refreshes->value as $key => $refresh) {
                        if ($refresh->status === 'Completed') {
                            try {
                                return \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $refresh->endTime, new DateTimeZone('UTC'));
                            } catch (\Exception $e) {
                                Debugger::log($e->getMessage(),'error');
                                return 'error parsing date';
                            }
                        }
                    }
                } else {
                    return 'no success refreshes found';
                }
            }

            return 'never';
        });
    }

    /**
     * @example https://learn.microsoft.com/en-us/rest/api/power-bi/groups/get-groups
     */
    public function getGroups($forSelectbox = false): ?array
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/groups';

        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        if (!empty($data) && isset($data->value)) {
            if ($forSelectbox) {
                $workspaces = [self::IN_CAPACITY => [], self::OUT_OF_CAPACITY => []];
                foreach ($data->value as $workspace) {
                    $workspaces[$workspace->isOnDedicatedCapacity ? self::IN_CAPACITY : self::OUT_OF_CAPACITY][$workspace->id] = $workspace->name;
                }
                return $workspaces;
            } else {
                return $data->value;
            }
        } else {
            return null;
        }
    }

    /**
     * @example https://learn.microsoft.com/en-us/rest/api/power-bi/reports/get-reports-in-group
     */
    public function getReports(string $workspaceId, $forSelectbox = false): ?array
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/groups/' . $workspaceId . '/reports';

        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        if (!empty($data) && isset($data->value)) {
            if ($forSelectbox) {
                $reports = [];
                foreach ($data->value as $report) {
                    $reports[$report->id] = $report->name;
                }
                return $reports;
            } else {
                return $data->value;
            }
        } else {
            return null;
        }
    }

    public function getPages(string $workspaceId, string $reportId, bool $forSelect = false): ?array
    {
        $guzzle = new \GuzzleHttp\Client();
        $url = $this->config['powerBiApiUrl'] . 'v1.0/myorg/groups/' . $workspaceId . '/reports/' . $reportId . '/pages';

        $headers = $this->getRequestHeader();

        $result = $guzzle->get($url, [
            'headers' => $headers,
        ]);
        $data = null;

        try {
            $data = Json::decode($result->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Debugger::barDump($e->getResponse(),'error');
        }

        if (!empty($data) && isset($data->value)) {
            if ($forSelect) {
                $pages = [];
                foreach ($data->value as $page) {
                    $pages[$page->name] = $page->displayName;
                }
                return $pages;
            } else {
                return $data->value;
            }
        } else {
            return null;
        }
    }


    protected function getRequestHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization'=> 'Bearer ' . $this->accessToken->access_token,
        ];
    }

}