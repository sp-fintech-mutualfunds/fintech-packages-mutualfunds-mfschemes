<?php

namespace Apps\Fintech\Packages\Mf\Schemes;

use Apps\Fintech\Packages\Mf\Extractdata\MfExtractdata;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemes;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesDetails;
use Apps\Fintech\Packages\Mf\Schemes\Settings;
use System\Base\BasePackage;

class MfSchemes extends BasePackage
{
    protected $modelToUse = AppsFintechMfSchemes::class;

    protected $packageName = 'mfschemes';

    public $mfschemes;

    protected $settings = Settings::class;

    protected $schemes = [];

    public function getSchemeById(int $id)
    {
        if (isset($this->schemes[$id])) {
            return $this->schemes[$id];
        }

        $this->setFFRelations(true);

        $this->getFirst('id', $id);

        if ($this->model) {
            $scheme = $this->model->toArray();

            $scheme['details'] = [];
            if ($this->model->getdetails()) {
                $scheme['details'] = $this->model->getdetails()->toArray();
            }

            $scheme['navs'] = [];
            if ($this->model->getnavs()) {
                $scheme['navs'] = $this->model->getnavs()->toArray();
            }

            $scheme['category'] = [];
            if ($this->model->getcategory()) {
                $scheme['category'] = $this->model->getcategory()->toArray();
            }
            $scheme['amc'] = [];
            if ($this->model->getamc()) {
                $scheme['amc'] = $this->model->getamc()->toArray();
            }

            if (!isset($this->schemes[$id])) {
                $this->schemes[$id] = $scheme;
            }

            return $scheme;
        } else {
            if ($this->ffData) {

                if (!isset($this->schemes[$id])) {
                    $this->schemes[$id] = $this->ffData;
                }

                return $this->ffData;
            }
        }

        return null;
    }

    public function getMfTypeByIsin($isin)
    {
        if ($this->config->databasetype === 'db') {
            $conditions =
                [
                    'conditions'    => 'isin = :isin:',
                    'bind'          =>
                        [
                            'isin'  => $isin
                        ]
                ];
        } else {
            $conditions =
                [
                    'conditions'    => [
                        ['isin', '=', $isin]
                    ]
                ];
        }

        $mfscheme = $this->getByParams($conditions);

        if ($mfscheme && count($mfscheme) > 0) {
            return $mfscheme[0];
        }

        return false;
    }

    public function getMfTypeByAmfiCode($amfi_code)
    {
        if ($this->config->databasetype === 'db') {
            $conditions =
                [
                    'conditions'    => 'amfi_code = :amfi_code:',
                    'bind'          =>
                        [
                            'amfi_code'  => (int) $amfi_code
                        ]
                ];
        } else {
            $conditions =
                [
                    'conditions'    => [
                        ['amfi_code', '=', (int) $amfi_code]
                    ]
                ];
        }

        $mfscheme = $this->getByParams($conditions);

        if ($mfscheme && count($mfscheme) > 0) {
            return $mfscheme[0];
        }

        return false;
    }

    public function addMfSchemes($data)
    {
        //
    }

    public function updateMfSchemes($data)
    {
        //
    }

    public function removeMfSchemes($data)
    {
        //
    }

    public function getAvailableApis($getAll = false, $returnApis = true)
    {
        $apisArr = [];

        if (!$getAll) {
            $package = $this->getPackage();
            if (isset($package['settings']) &&
                isset($package['settings']['api_clients']) &&
                is_array($package['settings']['api_clients']) &&
                count($package['settings']['api_clients']) > 0
            ) {
                foreach ($package['settings']['api_clients'] as $key => $clientId) {
                    $client = $this->basepackages->apiClientServices->getApiById($clientId);

                    if ($client) {
                        array_push($apisArr, $client);
                    }
                }
            }
        } else {
            $apisArr = $this->basepackages->apiClientServices->getApiByAppType();
        }

        if (count($apisArr) > 0) {
            foreach ($apisArr as $api) {
                if ($api['category'] === 'providers') {
                    $useApi = $this->basepackages->apiClientServices->useApi([
                            'config' =>
                                [
                                    'id'           => $api['id'],
                                    'category'     => $api['category'],
                                    'provider'     => $api['provider'],
                                    'checkOnly'    => true//Set this to check if the API exists and can be instantiated.
                                ]
                        ]);

                    if ($useApi) {
                        $apiConfig = $useApi->getApiConfig();

                        $apis[$api['id']]['id'] = $apiConfig['id'];
                        $apis[$api['id']]['name'] = $apiConfig['name'];
                        $apis[$api['id']]['provider'] = $apiConfig['provider'];
                        $apis[$api['id']]['data']['url'] = $apiConfig['api_url'];
                    }
                }
            }
        }

        if ($returnApis) {
            return $apis ?? [];
        }

        return $apisArr;
    }

    public function getSchemeInfo($data)
    {
        if (!isset($data['scheme_id'])) {
            $this->addResponse('Scheme ID not set', 1);

            return false;
        }

        try {
            $scheme = $this->getById($data['scheme_id']);

            if ($scheme) {
                $data['sync'] = 'getSchemeDetails';

                $data['isin'] = $scheme['isin'];

                $mfExtractdataPackage = new MfExtractdata;

                $remoteScheme = $mfExtractdataPackage->sync($data);

                $details = [];

                if ($remoteScheme && count($remoteScheme) > 0) {
                    $details['amfi_code'] = $scheme['amfi_code'];

                    if (isset($remoteScheme['code'])) {
                        $details['vendor_code'] = $remoteScheme['code'];
                    }
                    if (isset($remoteScheme['investment_objective'])) {
                        $details['investment_objective'] = $remoteScheme['investment_objective'];
                    }
                    if (isset($remoteScheme['crisil_rating'])) {
                        $details['crisil_rating'] = $remoteScheme['crisil_rating'];
                    }
                    if (isset($remoteScheme['start_date'])) {
                        $details['start_date'] = $remoteScheme['start_date'];
                    }
                    if (isset($remoteScheme['expense_ratio'])) {
                        $details['expense_ratio'] = $remoteScheme['expense_ratio'];
                    }
                    if (isset($remoteScheme['expense_ratio_date'])) {
                        $details['expense_ratio_date'] = $remoteScheme['expense_ratio_date'];
                    }

                    if (isset($remoteScheme['lock_in_period'])) {
                        $details['lock_in_period'] = $remoteScheme['lock_in_period'];
                    }
                    if (isset($remoteScheme['tax_period'])) {
                        $details['tax_period'] = $remoteScheme['tax_period'];
                    }

                    if (isset($remoteScheme['direct'])) {
                        $details['direct'] = strtolower($remoteScheme['direct']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['switch_allowed'])) {
                        $details['switch_allowed'] = strtolower($remoteScheme['switch_allowed']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['stp_flag'])) {
                        $details['stp_flag'] = strtolower($remoteScheme['stp_flag']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['swp_flag'])) {
                        $details['swp_flag'] = strtolower($remoteScheme['swp_flag']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['instant'])) {
                        $details['instant'] = strtolower($remoteScheme['instant']) === 'y' ? true : false;
                    }

                    if (isset($remoteScheme['lump_available'])) {
                        $details['lump_available'] = strtolower($remoteScheme['lump_available']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['lump_min'])) {
                        $details['lump_min'] = $remoteScheme['lump_min'];
                    }
                    if (isset($remoteScheme['lump_min_additional'])) {
                        $details['lump_min_additional'] = $remoteScheme['lump_min_additional'];
                    }
                    if (isset($remoteScheme['lump_max'])) {
                        $details['lump_max'] = $remoteScheme['lump_max'];
                    }
                    if (isset($remoteScheme['lump_multiplier'])) {
                        $details['lump_multiplier'] = $remoteScheme['lump_multiplier'];
                    }

                    if (isset($remoteScheme['sip_available'])) {
                        $details['sip_available'] = strtolower($remoteScheme['sip_available']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['sip_min'])) {
                        $details['sip_min'] = $remoteScheme['sip_min'];
                    }
                    if (isset($remoteScheme['sip_max'])) {
                        $details['sip_max'] = $remoteScheme['sip_max'];
                    }
                    if (isset($remoteScheme['sip_multiplier'])) {
                        $details['sip_multiplier'] = $remoteScheme['sip_multiplier'];
                    }
                    if (isset($remoteScheme['sip_maximum_gap'])) {
                        $details['sip_maximum_gap'] = $remoteScheme['sip_maximum_gap'];
                    }

                    if (isset($remoteScheme['redemption_allowed'])) {
                        $details['redemption_allowed'] = strtolower($remoteScheme['redemption_allowed']) === 'y' ? true : false;
                    }
                    if (isset($remoteScheme['redemption_amount_multiple'])) {
                        $details['redemption_amount_multiple'] = $remoteScheme['redemption_amount_multiple'];
                    }
                    if (isset($remoteScheme['redemption_amount_minimum'])) {
                        $details['redemption_amount_minimum'] = $remoteScheme['redemption_amount_minimum'];
                    }
                    if (isset($remoteScheme['redemption_quantity_multiple'])) {
                        $details['redemption_quantity_multiple'] = $remoteScheme['redemption_quantity_multiple'];
                    }
                    if (isset($remoteScheme['redemption_quantity_minimum'])) {
                        $details['redemption_quantity_minimum'] = $remoteScheme['redemption_quantity_minimum'];
                    }
                }

                if (count($details) > 0) {
                    $apis = $this->getAvailableApis();

                    if (isset($apis[$data['api_id']]['provider'])) {
                        $scheme['vendor'] = $apis[$data['api_id']]['provider'];

                        $this->update($scheme);
                    }

                    $detailsModel = new AppsFintechMfSchemesDetails;

                    if ($this->config->databasetype === 'db') {
                        $dbDetails = $detailsModel::findFirst(['amfi_code = ' . $scheme['amfi_code']]);
                    } else {
                        $detailsStore = $this->ff->store($detailsModel->getSource());

                        $dbDetails = $detailsStore->findOneBy(['amfi_code', '=', $scheme['amfi_code']]);
                    }

                    if ($dbDetails) {
                        $dbDetails = array_replace($dbDetails, $details);

                        $detailsStore->update($dbDetails);
                    } else {
                        $detailsStore->insert($details);
                    }

                    $this->addResponse('Retrieved information from remote successfully', 0, ['details' => $details]);

                    return true;
                }
            }

            $this->addResponse('Unable to retrieved information from remote.', 1);
        } catch (\throwable $e) {
            $this->addResponse($e->getMessage(), 1);
        }
    }

    public function searchSchemesForAMC($data)
    {
        if (!isset($data['amc_id'])) {
            $this->addResponse('AMC ID not set', 1);

            return false;
        }

        if ($this->config->databasetype === 'db') {
            $conditions =
                [
                    'conditions'    => 'amc_id = :amc_id:',
                    'bind'          =>
                        [
                            'amc_id'       => (int) $data['amc_id'],
                        ]
                ];
        } else {
            $conditions =
                [
                    'conditions'    => ['amc_id', '=', (int) $data['amc_id']]
                ];
        }

        $schemesArr = $this->getByParams($conditions);

        if ($schemesArr && count($schemesArr) > 0) {
            $schemes = [];

            foreach ($schemesArr as $scheme) {
                if (str_contains($scheme['isin'], 'INFINF')) {//Unknwon ISIN
                    continue;
                }

                if (str_contains(strtolower($scheme['name']), strtolower($data['search']))) {
                    $schemes[$scheme['id']]['id'] = $scheme['id'];
                    $schemes[$scheme['id']]['amfi_code'] = $scheme['amfi_code'];
                    $schemes[$scheme['id']]['name'] = $scheme['name'];
                    $schemes[$scheme['id']]['isin'] = $scheme['isin'];
                }
            }

            $this->addResponse('Found ' . count($schemes) . ' Schemes', 0, ['schemes' => $schemes]);

            return $schemes;
        }

        $this->addResponse('Found 0 Schemes', 0, ['schemes' => []]);

        return [];
    }

    public function getSchemeLatestNav($schemeId)
    {
        $scheme = $this->getSchemeById($schemeId);

        if ($scheme && isset($scheme['navs']['latest_nav'])) {
            return $scheme['navs']['latest_nav'];
        }

        return false;
    }

    public function getSchemeFromAmfiCodeOrSchemeId(&$data, $includeNavs = true)
    {
        if (isset($data['scheme_id']) && $data['scheme_id'] !== '') {
            if (isset($this->schemes[$data['scheme_id']])) {
                $scheme = [$this->schemes[$data['scheme_id']]];
            } else {
                $scheme = [$this->getSchemeById((int) $data['scheme_id'])];
            }
        } else if (isset($data['amfi_code']) && $data['amfi_code'] !== '') {
            if ($this->config->databasetype === 'db') {
                $conditions =
                    [
                        'conditions'    => 'amfi_code = :amfi_code:',
                        'bind'          =>
                            [
                                'amfi_code'       => (int) $data['amfi_code'],
                            ]
                    ];
            } else {
                $conditions =
                    [
                        'conditions'    => ['amfi_code', '=', (int) $data['amfi_code']]
                    ];
            }

            $scheme = $this->getByParams($conditions);

            if ($scheme && isset($scheme[0])) {
                if (isset($this->schemes[$scheme[0]['id']])) {
                    $scheme = [$this->schemes[$scheme[0]['id']]];
                } else {
                    $scheme = [$this->getSchemeById((int) $scheme[0]['id'])];
                }
            }
        }

        if (isset($scheme) && isset($scheme[0])) {
            $scheme = $scheme[0];

            $data['scheme_id'] = (int) $scheme['id'];

            if (!$includeNavs) {
                if (isset($scheme['navs'])) {
                    unset($scheme['navs']);
                }
            }

            return $scheme;
        }

        return false;
    }
}