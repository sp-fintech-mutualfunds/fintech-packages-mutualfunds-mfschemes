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

    public function getSchemeById(int $id)
    {
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

            return $scheme;
        } else {
            if ($this->ffData) {
                $this->ffData = $this->jsonData($this->ffData, true);

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
                            'amfi_code'  => $amfi_code
                        ]
                ];
        } else {
            $conditions =
                [
                    'conditions'    => [
                        ['amfi_code', '=', $amfi_code]
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
        $mfschemes = $this->getById($id);

        if ($mfschemes) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }

    public function removeMfSchemes($data)
    {
        $mfschemes = $this->getById($id);

        if ($mfschemes) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
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

                    $dbDetails = $detailsStore->findOneBy(['amfi_code', '=', (int) $scheme['amfi_code']]);
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

            $this->addResponse('Unable to retrieved information from remote.', 1);
        }
    }
}