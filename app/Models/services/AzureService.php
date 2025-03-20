<?php
namespace App\Models\Service;

use App\Models\BaseService;
use Nette\Caching\Cache;
use Nette\Utils\Json;
use Tracy\Debugger;

class AzureService extends BaseService
{
    protected ?object $accessToken = null;

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
        $embedParams = $this->getEmbedParamsForSingleReport($workspaceId, $reportId);
        $embedToken = $this->getEmbedTokenForSingleReportSingleWorkspace($reportId, [$embedParams->datasetId], $workspaceId);

        $embedParams->embedToken = $embedToken->token;
        $embedParams->expiration = $embedToken->expiration;

        return $embedParams;
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




    protected function getRequestHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization'=> 'Bearer ' . $this->accessToken->access_token,
        ];
    }

}