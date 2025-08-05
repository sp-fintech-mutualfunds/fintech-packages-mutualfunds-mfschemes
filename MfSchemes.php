<?php

namespace Apps\Fintech\Packages\Mf\Schemes;

use Apps\Fintech\Packages\Mf\Extractdata\MfExtractdata;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemes;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesAll;
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

    public function getSchemeById(int $id, $includeNavs = true, $includeNavsChunks = true, $includeNavsRR = true)
    {
        if (isset($this->schemes[$id])) {
            if (!$includeNavs) {
                unset($this->schemes[$id]['navs']);
            }

            if (!$includeNavsChunks) {
                unset($this->schemes[$id]['navs_chunks']);
            }

            if (!$includeNavsRR) {
                unset($this->schemes[$id]['rolling_returns']);
            }

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

            $scheme['navs_chunks'] = [];
            if ($this->model->getnavs_chunks()) {
                $scheme['navs_chunks'] = $this->model->getnavs_chunks()->toArray();
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

            if (!$includeNavs) {
                unset($scheme['navs']);
            }

            if (!$includeNavsChunks) {
                unset($scheme['navs_chunks']);
            }

            return $scheme;
        } else {
            if ($this->ffData) {
                if (!isset($this->schemes[$id])) {
                    $this->schemes[$id] = $this->ffData;
                }

                if (!$includeNavs) {
                    unset($this->ffData['navs']);
                }

                if (!$includeNavsChunks) {
                    unset($this->ffData['navs_chunks']);
                }

                if (!$includeNavsRR) {
                    unset($this->ffData['rolling_returns']);
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

    // public function getMfTypeByAmfiCode($amfi_code)
    // {
    //     if ($this->config->databasetype === 'db') {
    //         $conditions =
    //             [
    //                 'conditions'    => 'amfi_code = :amfi_code:',
    //                 'bind'          =>
    //                     [
    //                         'amfi_code'  => (int) $amfi_code
    //                     ]
    //             ];
    //     } else {
    //         $conditions =
    //             [
    //                 'conditions'    => [
    //                     ['amfi_code', '=', (int) $amfi_code]
    //                 ]
    //             ];
    //     }

    //     $mfscheme = $this->getByParams($conditions);

    //     if ($mfscheme && count($mfscheme) > 0) {
    //         return $mfscheme[0];
    //     }

    //     return false;
    // }

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
                if (str_contains(strtolower($scheme['name']), strtolower($data['search']))) {
                    $schemes[$scheme['id']]['id'] = $scheme['id'];
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

    public function searchSchemesForCategory($data)
    {
        if (!isset($data['category_id'])) {
            $this->addResponse('Category ID not set', 1);

            return false;
        }

        if ($this->config->databasetype === 'db') {
            $conditions =
                [
                    'conditions'    => 'category_id = :category_id:',
                    'bind'          =>
                        [
                            'category_id'       => (int) $data['category_id'],
                        ]
                ];
        } else {
            $conditions =
                [
                    'conditions'    => ['category_id', '=', (int) $data['category_id']]
                ];
        }

        $schemesArr = $this->getByParams($conditions);

        if ($schemesArr && count($schemesArr) > 0) {
            $schemes = [];

            foreach ($schemesArr as $scheme) {
                if (str_contains(strtolower($scheme['name']), strtolower($data['search']))) {
                    $schemes[$scheme['id']] = $scheme;
                }
            }

            $this->addResponse('Found ' . count($schemes) . ' Schemes', 0, ['schemes' => $schemes]);

            return $schemes;
        }

        $this->addResponse('Found 0 Schemes', 0, ['schemes' => []]);

        return [];
    }

    public function getSchemeNavByDate($scheme, $date = null, $first = false, $latest = false)
    {
        if ($first) {
            $date = $scheme['start_date'];
        } else if ($latest) {
            $date = $scheme['navs_last_updated'];
        }

        if (!$date) {
            $this->addResponse('Please provide date!', 1);

            return false;
        }

        $dates = explode('-', $date);

        try {
            $file = 'apps/Fintech/Packages/Mf/Extractdata/Data/navsindex/' . $scheme['id'] . '/' . $dates[0] . '/' . $dates[1] . '/' . $dates[2] . '.json';

            if ($this->localContent->fileExists($file)) {
                $nav = $this->helper->decode($this->localContent->read($file), true);

                $this->addResponse('Ok', 0, ['date' => $date, 'nav' => $nav]);

                return $nav;
            } else {
                $this->addResponse('Navindex not available for: ' . $scheme['id'] . ' for date: ' . $date, 1);

                return false;
            }
        } catch (FilesystemException | UnableToCheckExistence | UnableToReadFile | \throwable $e) {
            $this->addResponse($e->getMessage(), 1);

            return false;
        }

        return false;
    }

    public function getSchemeFromAmfiCodeOrSchemeId(&$data, $includeNavs = false, $includeNavsChunks = false, $includeNavsRR = false)
    {
        if (isset($data['scheme_id']) && $data['scheme_id'] !== '') {
            $schemeId = (int) $data['scheme_id'];
        } else if (isset($data['amfi_code']) && $data['amfi_code'] !== '') {
            $schemeId = (int) $data['amfi_code'];
        }

        $scheme = $this->getSchemeById($schemeId, $includeNavs, $includeNavsChunks, $includeNavsRR);

        if ($scheme) {
            $data['scheme_id'] = (int) $scheme['id'];

            return $scheme;
        }

        return false;
    }

    public function getSchemeNavChunks($data)
    {
        $scheme = $this->getSchemeById((int) $data['scheme_id'], false, true, false);

        $responseData = [];
        $responseData['chunks'] = [];
        $responseData['trend'] = [];

        foreach (['totalDays', 'up', 'down', 'neutral', 'minus5', 'minus5tominus4',
                 'minus4tominus3', 'minus3tominus2', 'minus2tominus1', 'minus1to0',
                 '0', '0to1', '1to2', '2to3', '3to4', '4to5', 'plus5'] as $trend
        ) {
            $responseData['trend'][$trend] = 0;
        }

        if (isset($data['chunk_size']) &&
            isset($scheme['navs_chunks']['navs_chunks'][$data['chunk_size']])
        ) {
            if ($data['chunk_size'] === 'all') {
                $responseData['chunks'][$data['chunk_size']] = $scheme['navs_chunks']['navs_chunks'];
            } else {
                $responseData['chunks'][$data['chunk_size']] = $scheme['navs_chunks']['navs_chunks'][$data['chunk_size']];
            }

            $daysDiff = null;

            if (isset($data['start_date']) && isset($data['end_date'])) {
                if ($data['chunk_size'] === 'all') {
                    $responseData['chunks'][$data['chunk_size']] = $scheme['navs_chunks']['navs_chunks'][$data['chunk_size']];
                }

                // $daysDiff = 0;

                $start = (\Carbon\Carbon::parse($data['start_date']));
                $end = (\Carbon\Carbon::parse($data['end_date']));

                $daysDiff = $start->diffInDays($end);

                $datesKeys = array_keys($responseData['chunks'][$data['chunk_size']]);
                $startDateKey = array_search($data['start_date'], $datesKeys);

                $responseData['chunks'][$data['chunk_size']] = array_slice($responseData['chunks'][$data['chunk_size']], $startDateKey, $daysDiff);

                if ($startDateKey !== 0) {
                    $responseData['chunks'][$data['chunk_size']] = array_values($responseData['chunks'][$data['chunk_size']]);

                    $recalculatedChunks = [];

                    foreach ($responseData['chunks'][$data['chunk_size']] as $chunkKey => $chunk) {
                        if (!isset($recalculatedChunks[$chunk['date']])) {
                            $recalculatedChunks[$chunk['date']]['nav'] = $chunk['nav'];
                            $recalculatedChunks[$chunk['date']]['date'] = $chunk['date'];
                            $recalculatedChunks[$chunk['date']]['timestamp'] = \Carbon\Carbon::parse($chunk['date'])->timestamp;

                            if ($chunkKey !== 0) {
                                $previousDay = $responseData['chunks'][$data['chunk_size']][$chunkKey - 1];

                                $recalculatedChunks[$chunk['date']]['diff'] =
                                    numberFormatPrecision($chunk['nav'] - $this->helper->first($responseData['chunks'][$data['chunk_size']])['nav'], 4);
                                $recalculatedChunks[$chunk['date']]['diff_percent'] =
                                    numberFormatPrecision(($chunk['nav'] * 100 / $this->helper->first($responseData['chunks'][$data['chunk_size']])['nav'] - 100), 2);

                                $recalculatedChunks[$chunk['date']]['trajectory'] = '-';
                                if ($chunk['nav'] > $previousDay['nav']) {
                                    $recalculatedChunks[$chunk['date']]['trajectory'] = 'up';
                                } else {
                                    $recalculatedChunks[$chunk['date']]['trajectory'] = 'down';
                                }

                                $recalculatedChunks[$chunk['date']]['diff_since_inception'] =
                                    numberFormatPrecision($chunk['nav'] - $this->helper->first($responseData['chunks'][$data['chunk_size']])['nav'], 4);
                                $recalculatedChunks[$chunk['date']]['diff_percent_since_inception'] =
                                    numberFormatPrecision(($chunk['nav'] * 100 / $this->helper->first($responseData['chunks'][$data['chunk_size']])['nav'] - 100), 2);
                            } else {
                                $recalculatedChunks[$chunk['date']]['diff'] = 0;
                                $recalculatedChunks[$chunk['date']]['diff_percent'] = 0;

                                $recalculatedChunks[$chunk['date']]['trajectory'] = '-';

                                $recalculatedChunks[$chunk['date']]['diff_since_inception'] = 0;
                                $recalculatedChunks[$chunk['date']]['diff_percent_since_inception'] = 0;
                            }
                        }
                    }
                    $responseData['chunks'][$data['chunk_size']] = $recalculatedChunks;
                }
            }

            $this->generateTrendData($scheme, $responseData, $data, $daysDiff);

            $this->addResponse('Ok', 0, $responseData);

            return $responseData;
        } else if ($data['range'] &&
                   isset($scheme['navs_chunks']['navs_chunks']['all'])
        ) {
            $data['chunk_size'] = 'range';

            if (count($data['range']) < 2) {
                $this->addResponse('Please provide correct range dates. Provide both, start and end date.', 1);

                return false;
            }

            try {
                $start = (\Carbon\Carbon::parse($data['range'][0]));
                $end = (\Carbon\Carbon::parse($data['range'][1]));

                if ($end->lt($start)) {
                    $this->addResponse('Please provide correct range dates. End date is less than start date', 1);

                    return false;
                }

                $daysDiff = $start->diffInDays($end);

                if (!isset($scheme['navs_chunks']['navs_chunks']['all'][$data['range'][0]]) ||
                    !isset($scheme['navs_chunks']['navs_chunks']['all'][$data['range'][1]])
                ) {
                    $this->addResponse('Please provide correct range dates. Navs for date not present.', 1);

                    return false;
                }

                $datesKeys = array_keys($scheme['navs_chunks']['navs_chunks']['all']);
                $startDateKey = array_search($data['range'][0], $datesKeys);
                $responseData['chunks'][$data['chunk_size']] = array_slice($scheme['navs_chunks']['navs_chunks']['all'], $startDateKey, $daysDiff + 1);

                if (count($responseData['chunks']) > 0) {
                    $firstChunk = $this->helper->first($responseData['chunks'][$data['chunk_size']]);

                    foreach ($responseData['chunks'][$data['chunk_size']] as $chunkDate => $chunk) {
                        $responseData['chunks'][$data['chunk_size']][$chunkDate]['diff'] = numberFormatPrecision($chunk['nav'] - $firstChunk['nav'], 4);
                        $responseData['chunks'][$data['chunk_size']][$chunkDate]['diff_percent'] = numberFormatPrecision(($chunk['nav'] * 100 / $firstChunk['nav'] - 100), 2);
                    }

                    $this->generateTrendData($scheme, $responseData, $data, $daysDiff);

                    $this->addResponse('Ok', 0, $responseData);

                    return $responseData;
                }
            } catch (\throwable $e) {
                trace([$e]);
                $this->addResponse('Exception: ' . $e->getMessage(), 1);

                return false;
            }
        }

        $this->addResponse('No data found', 1);
    }

    protected function generateTrendData($scheme, &$responseData, $data, $daysDiff = null)
    {
        if (count($responseData['chunks'][$data['chunk_size']]) !== count($scheme['navs_chunks']['navs_chunks']['all'])) {
            $datesKeys = array_keys($scheme['navs_chunks']['navs_chunks']['all']);
            $startDateKey = array_search($this->helper->firstKey($responseData['chunks'][$data['chunk_size']]), $datesKeys);
            if ($daysDiff) {
                $trendChunks = array_slice($scheme['navs_chunks']['navs_chunks']['all'], $startDateKey, $daysDiff + 1);
            } else {
                $trendChunks = array_slice($scheme['navs_chunks']['navs_chunks']['all'], $startDateKey);
            }
        } else {
            $trendChunks = $scheme['navs_chunks']['navs_chunks']['all'];
        }

        $responseData['trend']['totalDays'] = count($trendChunks);

        foreach ($trendChunks as $trendDate => $trendValue) {
            if (isset($trendValue['diff_percent']) &&
                $trendValue['diff_percent'] == 0
            ) {
                $responseData['trend']['neutral']++;
            } else {
                if (isset($trendValue['trajectory']) &&
                    $trendValue['trajectory'] === 'up'
                ) {
                    $responseData['trend']['up']++;
                } else if (isset($trendValue['trajectory']) &&
                           $trendValue['trajectory'] === 'down'
                ) {
                    $responseData['trend']['down']++;
                }
            }

            if (isset($trendValue['diff_percent']) &&
                $trendValue['diff_percent'] < 0
            ) {
                $trendValue['diff_percent'] = abs($trendValue['diff_percent']);

                if ($trendValue['diff_percent'] > 0 && $trendValue['diff_percent'] <= 1) {
                    $responseData['trend']['minus1to0']++;
                } else if ($trendValue['diff_percent'] > 1 && $trendValue['diff_percent'] <= 2) {
                    $responseData['trend']['minus2tominus1']++;
                } else if ($trendValue['diff_percent'] > 2 && $trendValue['diff_percent'] <= 3) {
                    $responseData['trend']['minus3tominus2']++;
                } else if ($trendValue['diff_percent'] > 3 && $trendValue['diff_percent'] <= 4) {
                    $responseData['trend']['minus4tominus3']++;
                } else if ($trendValue['diff_percent'] > 4 && $trendValue['diff_percent'] <= 5) {
                    $responseData['trend']['minus5tominus4']++;
                } else if ($trendValue['diff_percent'] > 5) {
                    $responseData['trend']['minus5']++;
                }
            } else if (isset($trendValue['diff_percent']) &&
                       $trendValue['diff_percent'] > 0
            ) {
                if ($trendValue['diff_percent'] > 0 && $trendValue['diff_percent'] <= 1) {
                    $responseData['trend']['0to1']++;
                } else if ($trendValue['diff_percent'] > 1 && $trendValue['diff_percent'] <= 2) {
                    $responseData['trend']['1to2']++;
                } else if ($trendValue['diff_percent'] > 2 && $trendValue['diff_percent'] <= 3) {
                    $responseData['trend']['2to3']++;
                } else if ($trendValue['diff_percent'] > 3 && $trendValue['diff_percent'] <= 4) {
                    $responseData['trend']['3to4']++;
                } else if ($trendValue['diff_percent'] > 4 && $trendValue['diff_percent'] <= 5) {
                    $responseData['trend']['4to5']++;
                } else if ($trendValue['diff_percent'] > 5) {
                    $responseData['trend']['plus5']++;
                }
            } else {
                $responseData['trend']['0']++;
            }
        }
    }

    public function getSchemeRollingReturns($data)
    {
        if (!isset($data['scheme_id'])) {
            $this->addResponse('Please provide Scheme ID', 1);

            return false;
        }

        if (!isset($data['rr_period'])) {
            $this->addResponse('Please provide Rolling Return Period', 1);

            return false;
        }

        $scheme = $this->getSchemeById((int) $data['scheme_id'], false, false, true);

        if (!isset($scheme['rolling_returns'][$data['rr_period']])) {
            $this->addResponse('Rolling Returns for period not available.', 1);

            return false;
        }

        if (!isset($data['start_date'])) {
            $this->addResponse('Please provide Rolling Return Start Date', 1);

            return false;
        }

        try {
            $dataStartDate = \Carbon\Carbon::parse($data['start_date']);
            $startDate = \Carbon\Carbon::parse($scheme['start_date']);
            $endDate = \Carbon\Carbon::parse($scheme['navs_last_updated']);

            if ($dataStartDate->lt($startDate)) {//If date is lt scheme start date.
                $data['start_date'] = $startDate->toDateString();
            }

            if ($dataStartDate->gt($endDate)) {//If date is gt scheme end date.
                $this->addResponse('Please provide correct Rolling Return start date', 1);

                return false;
            }

            if ($data['rr_period'] === 'year') {
                $endDate->subYear();
            } else if ($data['rr_period'] === 'two_year') {
                $endDate->subYear(2);
            } else if ($data['rr_period'] === 'three_year') {
                $endDate->subYear(3);
            } else if ($data['rr_period'] === 'five_year') {
                $endDate->subYear(5);
            } else if ($data['rr_period'] === 'seven_year') {
                $endDate->subYear(7);
            } else if ($data['rr_period'] === 'ten_year') {
                $endDate->subYear(10);
            } else if ($data['rr_period'] === 'fifteen_year') {
                $endDate->subYear(15);
            }

            if ($dataStartDate->gt($endDate)) {//If date is gt selected time period end date.
                $this->addResponse('Please provide correct Rolling Return start date', 1);

                return false;
            }
        } catch (\throwable $e) {
            $this->addResponse('Please provide correct Rolling Return start date', 1);

            return false;
        }

        $rrKeys = array_keys($scheme['rolling_returns'][$data['rr_period']]);

        $rrStartDateKey = array_search($data['start_date'], $rrKeys);

        $rrData = array_slice($scheme['rolling_returns'][$data['rr_period']], $rrStartDateKey);

        if (count($rrData) > 0) {
            $totalRRData = count($rrData);

            $cagrs = [];
            $distribution['negative'] = 0;
            $distribution['0-8'] = 0;
            $distribution['8-10'] = 0;
            $distribution['10-12'] = 0;
            $distribution['12-15'] = 0;
            $distribution['15-20'] = 0;
            $distribution['20+'] = 0;

            foreach ($rrData as $date => $rr) {
                array_push($cagrs, $rr['cagr']);

                if ($rr['cagr'] < 0) {
                    $distribution['negative']++;
                } else if ($rr['cagr'] > 0 && $rr['cagr'] < 8) {
                    $distribution['0-8']++;
                } else if ($rr['cagr'] > 8 && $rr['cagr'] < 10) {
                    $distribution['8-10']++;
                } else if ($rr['cagr'] > 10 && $rr['cagr'] < 12) {
                    $distribution['10-12']++;
                } else if ($rr['cagr'] > 12 && $rr['cagr'] < 15) {
                    $distribution['12-15']++;
                } else if ($rr['cagr'] > 15 && $rr['cagr'] < 20) {
                    $distribution['15-20']++;
                } else if ($rr['cagr'] >= 20) {
                    $distribution['20+']++;
                }
            }

            $responseData['statistics']['total'] = count($cagrs);
            $responseData['statistics']['average'] = numberFormatPrecision(\MathPHP\Statistics\Average::mean($cagrs), 2);
            $responseData['statistics']['median'] = numberFormatPrecision(\MathPHP\Statistics\Average::median($cagrs), 2);
            $responseData['statistics']['max'] = numberFormatPrecision($cagrs[\MathPHP\Search::argMax($cagrs)], 2);
            $responseData['statistics']['min'] = numberFormatPrecision($cagrs[\MathPHP\Search::argMin($cagrs)], 2);
            if ($distribution['negative'] > 0) {
                $distribution['negative'] = numberFormatPrecision($distribution['negative'] * 100 / $totalRRData, 2);
            } else {
                $distribution['negative'] = 0.0;
            }
            if ($distribution['0-8'] > 0) {
                $distribution['0-8'] = numberFormatPrecision($distribution['0-8'] * 100 / $totalRRData, 2);
            } else {
                $distribution['0-8'] = 0.0;
            }
            if ($distribution['8-10'] > 0) {
                $distribution['8-10'] = numberFormatPrecision($distribution['8-10'] * 100 / $totalRRData, 2);
            } else {
                $distribution['8-10'] = 0.0;
            }
            if ($distribution['10-12'] > 0) {
                $distribution['10-12'] = numberFormatPrecision($distribution['10-12'] * 100 / $totalRRData, 2);
            } else {
                $distribution['10-12'] = 0.0;
            }
            if ($distribution['12-15'] > 0) {
                $distribution['12-15'] = numberFormatPrecision($distribution['12-15'] * 100 / $totalRRData, 2);
            } else {
                $distribution['12-15'] = 0.0;
            }
            if ($distribution['15-20'] > 0) {
                $distribution['15-20'] = numberFormatPrecision($distribution['15-20'] * 100 / $totalRRData, 2);
            } else {
                $distribution['15-20'] = 0.0;
            }
            if ($distribution['20+'] > 0) {
                $distribution['20+'] = numberFormatPrecision($distribution['20+'] * 100 / $totalRRData, 2);
            } else {
                $distribution['20+'] = 0.0;
            }
            $responseData['distribution'] = $distribution;

            $responseData['rrData'] = $rrData;

            $this->addResponse('Ok', 0, $responseData);

            return $responseData;
        }

        $this->addResponse('No rolling return data found', 1);
    }

    public function searchAllSchemes(string $schemeQueryString)
    {
        $this->setModelToUse(AppsFintechMfSchemesAll::class);

        if ($this->config->databasetype === 'db') {
            $schemes =
                $this->getByParams(
                    [
                        'conditions'    => 'name LIKE :sName:',
                        'bind'          => [
                            'sName'     => '%' . $schemeQueryString . '%'
                        ]
                    ]
                );
        } else {
            $schemes = $this->getByParams(['conditions' => ['name', 'LIKE', '%' . $schemeQueryString . '%']]);
        }

        $this->addResponse('Ok', 0, ['schemes' => $schemes]);

        return $schemes;
    }
}