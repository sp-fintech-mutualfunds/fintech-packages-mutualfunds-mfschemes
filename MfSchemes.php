<?php

namespace Apps\Fintech\Packages\Mf\Schemes;

use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemes;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesAll;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesDetails;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesSnapshots;
use Apps\Fintech\Packages\Mf\Schemes\Settings;
use Apps\Fintech\Packages\Mf\Tools\Extractdata\MfToolsExtractdata;
use Apps\Fintech\Packages\Mf\Tools\Patterns\MfToolsPatterns;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;
use System\Base\BasePackage;

class MfSchemes extends BasePackage
{
    protected $modelToUse = AppsFintechMfSchemes::class;

    protected $packageName = 'mfschemes';

    public $mfschemes;

    protected $settings = Settings::class;

    protected $schemes = [];

    protected $parsedCarbon = [];

    public function getSchemeById(
        int $id,
        $includeNavs = true, $includeNavsChunks = true, $includeNavsRR = true,
        $includeCustom = false, $includeCustomChunks = false, $includeCustomRR = false
    ) {
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

            if (!$includeCustom) {
                unset($this->schemes[$id]['custom']);
            }

            if (!$includeCustomChunks) {
                unset($this->schemes[$id]['custom_chunks']);
            }

            if (!$includeCustomRR) {
                unset($this->schemes[$id]['custom_rolling_returns']);
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

            $scheme['rolling_returns'] = [];
            if ($this->model->getrolling_returns()) {
                $scheme['rolling_returns'] = $this->model->getrolling_returns()->toArray();
            }

            $scheme['custom'] = [];
            if ($this->model->getcustom()) {
                $scheme['custom'] = $this->model->getcustom()->toArray();
            }

            $scheme['custom_chunks'] = [];
            if ($this->model->getcustom_chunks()) {
                $scheme['custom_chunks'] = $this->model->getcustom_chunks()->toArray();
            }

            $scheme['custom_rolling_returns'] = [];
            if ($this->model->getcustom_rolling_returns()) {
                $scheme['custom_rolling_returns'] = $this->model->getcustom_rolling_returns()->toArray();
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

            if (!$includeNavsRR) {
                unset($scheme['rolling_returns']);
            }

            if (!$includeCustom) {
                unset($scheme['custom']);
            }

            if (!$includeCustomChunks) {
                unset($scheme['custom_chunks']);
            }

            if (!$includeCustomRR) {
                unset($scheme['custom_rolling_returns']);
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

                if (!$includeCustom) {
                    unset($this->ffData['custom']);
                }

                if (!$includeCustomChunks) {
                    unset($this->ffData['custom_chunks']);
                }

                if (!$includeCustomRR) {
                    unset($this->ffData['custom_rolling_returns']);
                }

                return $this->ffData;
            }
        }

        return false;
    }

    public function getSchemeSnapshotById($id, $timeline, $includeNavsChunks = false, $includeNavsRR = false)
    {
        if ($includeNavsChunks || $includeNavsRR) {
            $scheme = $this->getSchemeById((int) $id, true, false, false);

            if (!$scheme) {
                return false;
            }
        }

        //Increase memory_limit to 1G as the process takes a bit of memory to process the array.
        if ((int) ini_get('memory_limit') < 1024) {
            ini_set('memory_limit', '1024M');
        }

        try {
            if ($this->localContent->fileExists('.ff/sp/apps_fintech_mf_schemes_snapshots/data/' . $id . '.json')) {
                $schemeSnapshot = $this->helper->decode($this->localContent->read('.ff/sp/apps_fintech_mf_schemes_snapshots/data/' . $id . '.json'), true);
            }
        } catch (FilesystemException | UnableToReadFile | UnableToCheckExistence | \throwable $e) {
            $this->addResponse($e->getMessage(), 1);

            return false;
        }

        if (!isset($schemeSnapshot)) {
            $schemeSnapshot = [];
            $schemeSnapshot['id'] = $id;
            $schemeSnapshot['snapshots'] = [];
            $schemeSnapshot['navs_chunks_ids'] = [];
            $schemeSnapshot['rolling_returns_ids'] = [];
        }

        if (!isset($schemeSnapshot['snapshots'][$timeline]) ||
            (isset($schemeSnapshot['snapshots'][$timeline]) &&
             !isset($schemeSnapshot['navs_chunks_ids'][$timeline]) ||
             !isset($schemeSnapshot['rolling_returns_ids'][$timeline]))
        ) {
            $schemeSnapshot['snapshots'][$timeline] = [];

            $mfToolsExtractDataPackage = $this->usePackage(MfToolsExtractdata::class);

            if (!isset($scheme)) {
                $this->getSchemeById($id, true, false, false);
            }

            $navs = $this->schemes[$id]['navs']['navs'];
            $navsKeys = array_keys($navs);
            $timelineDateKey = array_search($timeline, $navsKeys);

            $navs = array_slice($navs, 0, $timelineDateKey + 1);

            if (count($navs) > 0) {
                $dbNav = [];
                $dbNav['id'] = $id;
                $dbNav['last_updated'] = $timeline;
                $dbNav['navs'] = $navs;

                $mfToolsExtractDataPackage->createChunks($dbNav, ['get_all_navs' => 'true'], false, $schemeSnapshot);
                $mfToolsExtractDataPackage->createRollingReturns($dbNav, $id, ['get_all_navs' => 'true'], false, $schemeSnapshot);

                try {
                    $this->localContent->write('.ff/sp/apps_fintech_mf_schemes_snapshots/data/' . $id . '.json', $this->helper->encode($schemeSnapshot));
                } catch (FilesystemException | UnableToWriteFile | \throwable $e) {
                    $this->addResponse($e->getMessage(), 1);

                    return false;
                }
            }
        }

        if ($includeNavsChunks || $includeNavsRR) {
            $scheme = array_replace($scheme, $schemeSnapshot['snapshots'][$timeline]);

            if ($includeNavsChunks) {
                try {
                    if ($this->localContent->fileExists('.ff/sp/apps_fintech_mf_schemes_snapshots_navs_chunks/data/' . $schemeSnapshot['navs_chunks_ids'][$timeline] . '.json')) {
                        $scheme['navs_chunks'] = $this->helper->decode($this->localContent->read('.ff/sp/apps_fintech_mf_schemes_snapshots_navs_chunks/data/' . $schemeSnapshot['navs_chunks_ids'][$timeline] . '.json'), true);
                    }
                } catch (FilesystemException | UnableToReadFile | UnableToCheckExistence | \throwable $e) {
                    $this->addResponse($e->getMessage(), 1);

                    return false;
                }

                $scheme['latest_nav'] = $this->helper->last($scheme['navs_chunks']['navs_chunks']['all'])['nav'];
                $scheme['navs_last_updated'] = $this->helper->last($scheme['navs_chunks']['navs_chunks']['all'])['date'];
            }

            if ($includeNavsRR) {
                try {
                    if ($this->localContent->fileExists('.ff/sp/apps_fintech_mf_schemes_snapshots_navs_rolling_returns/data/' . $schemeSnapshot['rolling_returns_ids'][$timeline] . '.json')) {
                        $scheme['rolling_returns'] = $this->helper->decode($this->localContent->read('.ff/sp/apps_fintech_mf_schemes_snapshots_navs_rolling_returns/data/' . $schemeSnapshot['rolling_returns_ids'][$timeline] . '.json'), true);
                    }
                } catch (FilesystemException | UnableToReadFile | UnableToCheckExistence | \throwable $e) {
                    $this->addResponse($e->getMessage(), 1);

                    return false;
                }
            }

            return $scheme;
        }

        return $schemeSnapshot['snapshots'][$timeline];
    }

    // protected function createSnapshotNavsChunks($id, $dbNav, $timeline)
    // {
    //     $chunks = [];
    //     $chunks['last_updated'] = $timeline;
    //     $chunks['navs_chunks']['all'] = $dbNav;

    //     $datesKeys = array_keys($chunks['navs_chunks']['all']);

    //     foreach (['week', 'month', 'threeMonth', 'sixMonth', 'year', 'threeYear', 'fiveYear', 'tenYear', 'fifteenYear', 'twentyYear', 'twentyFiveYear'] as $time) {
    //         $latestDate = \Carbon\Carbon::parse($this->helper->lastKey($chunks['navs_chunks']['all']));
    //         $timeDate = null;

    //         if ($time === 'week') {
    //             $timeDate = $latestDate->subDay(6)->toDateString();
    //         } else if ($time === 'month') {
    //             $timeDate = $latestDate->subMonth()->toDateString();
    //         } else if ($time === 'threeMonth') {
    //             $timeDate = $latestDate->subMonth(3)->toDateString();
    //         } else if ($time === 'sixMonth') {
    //             $timeDate = $latestDate->subMonth(6)->toDateString();
    //         } else if ($time === 'year') {
    //             $timeDate = $latestDate->subYear()->toDateString();
    //         } else if ($time === 'threeYear') {
    //             $timeDate = $latestDate->subYear(3)->toDateString();
    //         } else if ($time === 'fiveYear') {
    //             $timeDate = $latestDate->subYear(5)->toDateString();
    //         } else if ($time === 'tenYear') {
    //             $timeDate = $latestDate->subYear(10)->toDateString();
    //         } else if ($time === 'fifteenYear') {
    //             $timeDate = $latestDate->subYear(15)->toDateString();
    //         } else if ($time === 'twentyYear') {
    //             $timeDate = $latestDate->subYear(20)->toDateString();
    //         } else if ($time === 'twentyFiveYear') {
    //             $timeDate = $latestDate->subYear(25)->toDateString();
    //         }

    //         if (isset($chunks['navs_chunks']['all'][$timeDate])) {
    //             $timeDateKey = array_search($timeDate, $datesKeys);
    //             $timeDateChunks = array_slice($chunks['navs_chunks']['all'], $timeDateKey);

    //             if (count($timeDateChunks) > 0) {
    //                 $chunks['navs_chunks'][$time] = [];

    //                 foreach ($timeDateChunks as $timeDateChunkDate => $timeDateChunk) {
    //                     $chunks['navs_chunks'][$time][$timeDateChunkDate] = [];
    //                     $chunks['navs_chunks'][$time][$timeDateChunkDate]['date'] = $timeDateChunk['date'];
    //                     $chunks['navs_chunks'][$time][$timeDateChunkDate]['nav'] = $timeDateChunk['nav'];
    //                     $chunks['navs_chunks'][$time][$timeDateChunkDate]['diff'] =
    //                         numberFormatPrecision($timeDateChunk['nav'] - $this->helper->first($timeDateChunks)['nav'], 4);
    //                     $chunks['navs_chunks'][$time][$timeDateChunkDate]['diff_percent'] =
    //                         numberFormatPrecision(($timeDateChunk['nav'] * 100 / $this->helper->first($timeDateChunks)['nav'] - 100), 2);
    //                 }
    //             }
    //         }
    //     }

    //     trace([$chunks]);
    //     try {
    //         $this->localContent->write('.ff/sp/apps_fintech_mf_schemes_navs_chunks/data/' . $chunks['id'] . '.json', $this->helper->encode($chunks));
    //     } catch (FilesystemException | UnableToWriteFile | \throwable $e) {
    //         $this->addResponse($e->getMessage(), 1);

    //         return false;
    //     }

    //     return true;
    // }

    // protected function createSnapshotFromRollingReturns($id, $dbNav, $timeline)
    // {
    //     $schemeSnapshot = [];
    //     $schemeSnapshot['day_cagr'] = $this->helper->last($dbNav)['diff_percent'];
    //     $schemeSnapshot['day_trajectory'] = $this->helper->last($dbNav)['trajectory'];

    //     $latestDate = \Carbon\Carbon::parse($this->helper->lastKey($dbNav));
    //     $yearBefore = $latestDate->subYear()->toDateString();
    //     if (!isset($dbNav[$yearBefore])) {
    //         foreach (['year', 'two_year', 'three_year', 'five_year', 'seven_year', 'ten_year', 'fifteen_year', 'twenty_year', 'twenty_five_year'] as $rrTerm) {
    //             $schemeSnapshot[$rrTerm . '_rr'] = null;
    //             $schemeSnapshot[$rrTerm . '_cagr'] = null;
    //         }

    //         return $schemeSnapshot;
    //     }

    //     $dbNavNavs = [];

    //     foreach (['year', 'two_year', 'three_year', 'five_year', 'seven_year', 'ten_year', 'fifteen_year', 'twenty_year', 'twenty_five_year'] as $rrTerm) {
    //         $schemeSnapshot[$rrTerm . '_rr'] = null;
    //         $schemeSnapshot[$rrTerm . '_cagr'] = null;

    //         try {
    //             $toDate = \Carbon\Carbon::parse($timeline);

    //             if ($rrTerm === 'year') {
    //                 $fromDate = $toDate->subYear()->toDateString();
    //             } else if ($rrTerm === 'two_year') {
    //                 $fromDate = $toDate->subYear(2)->toDateString();
    //             } else if ($rrTerm === 'three_year') {
    //                 $fromDate = $toDate->subYear(3)->toDateString();
    //             } else if ($rrTerm === 'five_year') {
    //                 $fromDate = $toDate->subYear(5)->toDateString();
    //             } else if ($rrTerm === 'seven_year') {
    //                 $fromDate = $toDate->subYear(7)->toDateString();
    //             } else if ($rrTerm === 'ten_year') {
    //                 $fromDate = $toDate->subYear(10)->toDateString();
    //             } else if ($rrTerm === 'fifteen_year') {
    //                 $fromDate = $toDate->subYear(15)->toDateString();
    //             } else if ($rrTerm === 'twenty_year') {
    //                 $fromDate = $toDate->subYear(20)->toDateString();
    //             } else if ($rrTerm === 'twenty_five_year') {
    //                 $fromDate = $toDate->subYear(25)->toDateString();
    //             }

    //             if (isset($dbNav[$fromDate])) {
    //                 $dbNavNavs[$rrTerm] = $fromDate;
    //             } else {
    //                 $dbNavNavs[$rrTerm] = null;
    //             }
    //         } catch (\throwable $e) {
    //             trace([$e]);
    //             $this->addResponse($e->getMessage(), 1);

    //             return false;
    //         }
    //     }

    //     $rr = [];

    //     try {
    //         if ($this->localContent->fileExists('.ff/sp/apps_fintech_mf_schemes_navs_rolling_returns/data/' . $id . '.json')) {
    //             $rr = $this->helper->decode($this->localContent->read('.ff/sp/apps_fintech_mf_schemes_navs_rolling_returns/data/' . $id . '.json'), true);
    //         }
    //     } catch (FilesystemException | UnableToReadFile | UnableToCheckExistence | \throwable $e) {
    //         $this->addResponse($e->getMessage(), 1);

    //         return false;
    //     }

    //     foreach ($dbNavNavs as $rrTerm => $rrDate) {
    //         if ($rrDate) {
    //             if (!isset($rr[$rrTerm][$rrDate]) ||
    //                 (isset($rr[$rrTerm]) && count($rr[$rrTerm]) === 0)
    //             ) {
    //                 continue;
    //             }

    //             if ($rr[$rrTerm][$rrDate]['from'] === $rrDate &&
    //                 $rr[$rrTerm][$rrDate]['to'] === $timeline
    //             ) {
    //                 $schemeSnapshot[$rrTerm . '_cagr'] = $rr[$rrTerm][$rrDate]['cagr'];
    //             }
    //         }
    //     }

    //     //Calculate RR Average for timeframes. This will be used to narrow down our fund search.
    //     $rrCagrs = [];
    //     foreach ($rr as $rrTermType => $rrTermArr) {
    //         if (is_array($rrTermArr) && isset($dbNavNavs[$rrTermType])) {
    //             $rrKeys = array_keys($rrTermArr);
    //             $dbNavNavsDateKey = array_search($dbNavNavs[$rrTermType], $rrKeys);

    //             if ($dbNavNavsDateKey) {//We will not find an entry that falls on holiday/weekends.
    //                 $slicedRrTermArr = array_slice($rrTermArr, 0, $dbNavNavsDateKey + 1);

    //                 foreach ($slicedRrTermArr as $rrTermArrDate => $rrTermArrValue) {
    //                     if (!isset($rrCagrs[$rrTermType])) {
    //                         $rrCagrs[$rrTermType] = [];
    //                     }

    //                     $rrCagrs[$rrTermType][$rrTermArrDate] = $rrTermArrValue['cagr'];
    //                 }
    //             }
    //         }
    //     }

    //     if (count($rrCagrs) > 0) {
    //         foreach ($rrCagrs as $rrCagrTerm => $rrCagrArr) {
    //             $schemeSnapshot[$rrCagrTerm . '_rr'] = numberFormatPrecision(\MathPHP\Statistics\Average::mean($rrCagrArr), 2);
    //         }
    //     }

    //     return $schemeSnapshot;
    // }

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

                $mfExtractdataPackage = $this->usePackage(MfToolsExtractdata::class);

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
            $condition = 'category_id = :category_id:';

            if (isset($data['timeline'])) {
                $condition = $condition . ' AND start_date <= :timeline:';
            }

            $conditions =
                [
                    'conditions'    => $condition,
                    'bind'          =>
                        [
                            'category_id'       => (int) $data['category_id'],
                        ]
                ];

            if (isset($data['timeline'])) {
                $conditions['bind']['timeline'] = $data['timeline'];
            }
        } else {
            $conditions =
                [
                    'conditions'    => ['category_id', '=', (int) $data['category_id']]
                ];

            if (isset($data['timeline'])) {
                $conditions['conditions'] = [['category_id', '=', (int) $data['category_id']], ['start_date', '<=', $data['timeline']]];
            }
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

        if (isset($scheme['navs'][$date])) {
            return $scheme['navs'][$date];
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

    public function getSchemeNavChunks($data, $customChunks = false, $timeline = false)
    {
        if ($customChunks) {
            $scheme = $this->getSchemeById((int) $data['scheme_id'], false, false, false, false, true);

            if (!$scheme['custom_chunks'] || !isset($scheme['custom_chunks']['navs_chunks'])) {
                $this->addResponse('Custom chunks for scheme not found', 1, []);

                return false;
            }

            $chunks = $scheme['custom_chunks']['navs_chunks'];
        } else {
            if (!isset($this->schemes[$data['scheme_id']])) {
                if ($timeline) {
                    $scheme = $this->getSchemeSnapshotById((int) $data['scheme_id'], $timeline, true);
                } else {
                    $scheme = $this->getSchemeById((int) $data['scheme_id'], false, true, false);
                }
            } else {
                $scheme = $this->schemes[$data['scheme_id']];
            }

            $chunks = $scheme['navs_chunks']['navs_chunks'];
        }

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
            isset($chunks[$data['chunk_size']])
        ) {
            if ($data['chunk_size'] === 'all') {
                $responseData['chunks'][$data['chunk_size']] = $chunks;
            } else {
                $responseData['chunks'][$data['chunk_size']] = $chunks[$data['chunk_size']];
            }

            $daysDiff = null;

            if (isset($data['start_date']) && isset($data['end_date'])) {
                if ($data['chunk_size'] === 'all') {
                    $responseData['chunks'][$data['chunk_size']] = $chunks[$data['chunk_size']];
                }

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

            $this->generateTrendData($scheme, $responseData, $data, $daysDiff, $customChunks);

            $this->addResponse('Ok', 0, $responseData);

            return $responseData;
        } else if ($data['range'] && isset($chunks['all'])) {
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

                if (!isset($chunks['all'][$data['range'][0]]) ||
                    !isset($chunks['all'][$data['range'][1]])
                ) {
                    $this->addResponse('Please provide correct range dates. Navs for date not present.', 1);

                    return false;
                }

                $datesKeys = array_keys($chunks['all']);
                $startDateKey = array_search($data['range'][0], $datesKeys);
                $responseData['chunks'][$data['chunk_size']] = array_slice($chunks['all'], $startDateKey, $daysDiff + 1);

                if (count($responseData['chunks']) > 0) {
                    $firstChunk = $this->helper->first($responseData['chunks'][$data['chunk_size']]);

                    foreach ($responseData['chunks'][$data['chunk_size']] as $chunkDate => $chunk) {
                        $responseData['chunks'][$data['chunk_size']][$chunkDate]['diff'] = numberFormatPrecision($chunk['nav'] - $firstChunk['nav'], 4);
                        $responseData['chunks'][$data['chunk_size']][$chunkDate]['diff_percent'] = numberFormatPrecision(($chunk['nav'] * 100 / $firstChunk['nav'] - 100), 2);
                    }

                    $this->generateTrendData($scheme, $responseData, $data, $daysDiff, $customChunks);

                    $this->addResponse('Ok', 0, $responseData);

                    return $responseData;
                }
            } catch (\throwable $e) {
                $this->addResponse('Exception: ' . $e->getMessage(), 1);

                return false;
            }
        }

        $this->addResponse('No data found', 1);
    }

    protected function generateTrendData($scheme, &$responseData, $data, $daysDiff = null, $customChunks = false)
    {
        if ($customChunks) {
            $chunks = $scheme['custom_chunks']['navs_chunks'];
        } else {
            $chunks = $scheme['navs_chunks']['navs_chunks'];
        }

        if (count($responseData['chunks'][$data['chunk_size']]) !== count($chunks['all'])) {
            $datesKeys = array_keys($chunks['all']);
            $startDateKey = array_search($this->helper->firstKey($responseData['chunks'][$data['chunk_size']]), $datesKeys);
            if ($daysDiff) {
                $trendChunks = array_slice($chunks['all'], $startDateKey, $daysDiff + 1);
            } else {
                $trendChunks = array_slice($chunks['all'], $startDateKey);
            }
        } else {
            $trendChunks = $chunks['all'];
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

    public function getSchemeRollingReturns($data, $customRollingReturns = false, $timeline = false)
    {
        if (!isset($data['scheme_id'])) {
            $this->addResponse('Please provide Scheme ID', 1);

            return false;
        }

        if (!isset($data['rr_period'])) {
            $this->addResponse('Please provide Rolling Return Period', 1);

            return false;
        }

        if ($customRollingReturns) {
            $scheme = $this->getSchemeById((int) $data['scheme_id'], false, false, false, true, false, true);

            if (!isset($scheme['custom_rolling_returns']) || !isset($scheme['custom_rolling_returns'][$data['rr_period']])) {
                $this->addResponse('Custom rolling returns for scheme not found', 1, []);

                return false;
            }

            $rollingReturns = $scheme['custom_rolling_returns'];

            $scheme['start_date'] = $this->helper->first($scheme['custom']['navs'])['date'];
            $scheme['navs_last_updated'] = $this->helper->last($scheme['custom']['navs'])['date'];

            unset($scheme['custom']);
        } else {
            if ($timeline) {
                $scheme = $this->getSchemeSnapshotById((int) $data['scheme_id'], $timeline, false, true);
            } else {
                $scheme = $this->getSchemeById((int) $data['scheme_id'], false, false, true);
            }

            if (!isset($scheme['rolling_returns'][$data['rr_period']])) {
                $this->addResponse('Rolling Returns for period not available.', 1);

                return false;
            }

            $rollingReturns = $scheme['rolling_returns'];
        }

        if (!isset($data['start_date'])) {
            $this->addResponse('Please provide Rolling Return Start Date', 1);

            return false;
        }

        if (isset($data['compare_start_date'])) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $compareStartDate = \Carbon\Carbon::parse($data['compare_start_date']);

            if (($startDate)->lt($compareStartDate)) {
                $this->addResponse('Start date should not be before ' . $data['compare_start_date'], 1);

                return false;
            }
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
            } else if ($data['rr_period'] === 'twenty_year') {
                $endDate->subYear(20);
            } else if ($data['rr_period'] === 'twenty_five_year') {
                $endDate->subYear(25);
            }

            if ($dataStartDate->gt($endDate)) {//If date is gt selected time period end date.
                $this->addResponse('Please provide correct Rolling Return start date', 1);

                return false;
            }
        } catch (\throwable $e) {
            $this->addResponse('Please provide correct Rolling Return start date', 1);

            return false;
        }

        $rrKeys = array_keys($rollingReturns[$data['rr_period']]);

        $rrStartDateKey = array_search($data['start_date'], $rrKeys);

        $rrData = array_slice($rollingReturns[$data['rr_period']], $rrStartDateKey);

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

    public function generateCustomNav($data)
    {
        if (!isset($data['source']) ||
            (isset($data['source']) && $data['source'] === '') ||
            (isset($data['source']) &&
             ($data['source'] !== 'current_navs' && $data['source'] !== 'patterns' && $data['source'] !== 'fixed_linear' && $data['source'] !== 'fixed_random')
            )
        ) {
            $this->addResponse('Please provide correct source', 1);

            return;
        }

        if (!isset($data['startValue'])) {
            $data['startValue'] = 0;
        }

        $data['startValue'] = (float) $data['startValue'];

        $patterns = [];

        if ($data['startValue'] == 0) {
            $patterns[0] = 0;
        }

        if ($data['source'] === 'fixed_linear' || $data['source'] === 'fixed_random') {
            if (!isset($data['numberOfDays']) ||
                (isset($data['numberOfDays']) && $data['numberOfDays'] === '')
            ) {
                $this->addResponse('Please provide correct number of days', 1);

                return;
            }

            if (!isset($data['totalPercent']) ||
                (isset($data['totalPercent']) && $data['totalPercent'] === '')
            ) {
                $this->addResponse('Please provide correct total percent', 1);

                return;
            }

            $data['numberOfDays'] = (int) $data['numberOfDays'];
            $data['totalPercent'] = (float) $data['totalPercent'];

            if ($data['numberOfDays'] === 0) {
                $this->addResponse('Please provide correct number of days', 1);

                return;
            }

            if ($data['totalPercent'] == 0) {
                $this->addResponse('Please provide correct total percent', 1);

                return;
            }

            $perDay = $data['totalPercent'] / $data['numberOfDays'];

            for ($numberOfDays=0; $numberOfDays < $data['numberOfDays']; $numberOfDays++) {
                $patterns[$numberOfDays + 1] = numberFormatPrecision($perDay * ($numberOfDays + 1), 2);

                if ($data['startValue'] > 0) {
                    $patterns[$numberOfDays + 1] = numberFormatPrecision($data['startValue'] + $patterns[$numberOfDays + 1], 2);
                }
            }

            if ($data['source'] === 'fixed_linear') {
                $this->addResponse('Ok', 0, ['pattern' => array_values($patterns)]);

                return true;
            }

            if (!isset($data['between'])) {
                $data['between'] = ['-5', '5'];
            } else {
                $data['between'] = explode(',', $data['between']);
            }

            foreach ($patterns as $index => &$pattern) {
                if ($index === 0 ||
                    $index === $this->helper->lastKey($patterns)
                ) {
                    continue;
                }

                $pattern = numberFormatPrecision($pattern + (mt_rand((float) $data['between'][0] * 100, (float) $data['between'][1] * 100) / 100), 2);
            }
        } else if ($data['source'] === 'current_navs') {
            $scheme = $this->getSchemeById((int) $data['scheme_id'], true);

            if ($scheme && isset($scheme['navs']['navs']) && count($scheme['navs']['navs']) > 0) {
                $navs = array_values($scheme['navs']['navs']);

                for ($numberOfDays = 0; $numberOfDays < count($navs); $numberOfDays++) {
                    if (isset($navs[$numberOfDays + 1]['diff_percent_since_inception'])) {
                        $patterns[$numberOfDays + 1] = numberFormatPrecision($navs[$numberOfDays + 1]['diff_percent_since_inception'], 2);

                        if ($data['startValue'] > 0) {
                            $patterns[$numberOfDays + 1] = numberFormatPrecision($data['startValue'] + $patterns[$numberOfDays + 1], 2);
                        }
                    }
                }
            }
        } else if ($data['source'] === 'patterns') {
            if (!isset($data['pattern_id'])) {
                $this->addResponse('Please provide correct pattern ID', 1);

                return;
            }

            $mfToolsPatternsPackage = $this->usePackage(MfToolsPatterns::class);

            $mfToolsPattern = $mfToolsPatternsPackage->getById((int) $data['pattern_id']);

            if (!$mfToolsPattern || $mfToolsPattern && !isset($mfToolsPattern['pattern'])) {
                $this->addResponse('Please provide correct pattern ID', 1);

                return;
            }

            for ($numberOfDays = 0; $numberOfDays < count($mfToolsPattern['pattern']); $numberOfDays++) {
                if (isset($mfToolsPattern['pattern'][$numberOfDays + 1])) {
                    $patterns[$numberOfDays + 1] = numberFormatPrecision($mfToolsPattern['pattern'][$numberOfDays + 1], 2);

                    if ($data['startValue'] > 0) {
                        $patterns[$numberOfDays + 1] = numberFormatPrecision($data['startValue'] + $patterns[$numberOfDays + 1], 2);
                    }
                }
            }
        }

        $this->addResponse('Ok', 0, ['pattern' => array_values($patterns)]);

        return true;
    }

    public function updateSchemeCustomNavs($data)
    {
        if (!isset($data['custom_navs_percent']) ||
            (isset($data['custom_navs_percent']) && $data['custom_navs_percent'] === '')
        ) {
            $this->addResponse('Please provide custom navs', 1);

            return;
        }

        $scheme = $this->getSchemeById((int) $data['scheme_id'], true);

        if ($scheme && isset($scheme['navs']['navs']) && count($scheme['navs']['navs']) > 0) {
            $firstDate = \Carbon\Carbon::parse($this->helper->first($scheme['navs']['navs'])['date']);
            $firstNav = $this->helper->first($scheme['navs']['navs'])['nav'];
        } else {
            $firstDate = \Carbon\Carbon::parse('01-01-2007');
            $firstNav = 0;
        }

        $data['custom_navs_percent'] = explode(',', $data['custom_navs_percent']);

        $customNavs = [];
        $previousDay = [];

        array_walk($data['custom_navs_percent'], function($percent, $index) use (&$customNavs, $firstNav, $firstDate, &$previousDay) {
            $percent = (float) $percent;

            if ($index === 0) {
                $date = $firstDate->toDateString();

                $customNavs[$date] = [];
                $customNavs[$date]['nav'] = $firstNav;
                $customNavs[$date]['date'] = $date;
                $customNavs[$date]['timestamp'] = $firstDate->timestamp;

                $previousDay = $customNavs[$date];
            } else {
                $firstDate->addDay();
                $date = $firstDate->toDateString();

                $customNavs[$date] = [];
                $customNavs[$date]['nav'] = round($firstNav + (($percent * $firstNav) / 100), 2);
                $customNavs[$date]['date'] = $date;
                $customNavs[$date]['timestamp'] = $firstDate->timestamp;
                $customNavs[$date]['diff'] = numberFormatPrecision($customNavs[$date]['nav'] - $previousDay['nav'], 4);
                $customNavs[$date]['diff_percent'] = numberFormatPrecision(($customNavs[$date]['nav'] * 100 / $previousDay['nav']) - 100, 2);

                $customNavs[$date]['trajectory'] = '-';
                if ($customNavs[$date]['nav'] > $previousDay['nav']) {
                    $customNavs[$date]['trajectory'] = 'up';
                } else {
                    $customNavs[$date]['trajectory'] = 'down';
                }

                $customNavs[$date]['diff_since_inception'] = numberFormatPrecision($customNavs[$date]['nav'] - $firstNav, 4);
                $customNavs[$date]['diff_percent_since_inception'] = $percent;

                $previousDay = $customNavs[$date];
            }
        });

        try {
            $this->localContent->write('.ff/sp/apps_fintech_mf_schemes_custom/data/' . $scheme['id'] . '.json', $this->helper->encode(
                [
                    'id'            => $scheme['id'],
                    'lastUpdated'   => $this->helper->lastKey($customNavs),
                    'navs'          => $customNavs
                ]
            ));
        } catch (FilesystemException | UnableToWriteFile | \throwable $e) {
            $this->addResponse($e->getMessage(), 1);

            return false;
        }

        //Custom Chunks
        $chunks = [];
        $chunks['id'] = (int) $scheme['id'];
        $chunks['last_updated'] = $this->helper->lastKey($customNavs);
        $chunks['navs_chunks']['all'] = $customNavs;

        $datesKeys = array_keys($chunks['navs_chunks']['all']);

        foreach (['week', 'month', 'threeMonth', 'sixMonth', 'year', 'threeYear', 'fiveYear', 'tenYear', 'fifteenYear', 'twentyYear', 'twentyFiveYear', 'thirtyYear'] as $time) {
            $latestDate = \Carbon\Carbon::parse($this->helper->lastKey($chunks['navs_chunks']['all']));
            $timeDate = null;

            if ($time === 'week') {
                $timeDate = $latestDate->subDay(6)->toDateString();
            } else if ($time === 'month') {
                $timeDate = $latestDate->subMonth()->toDateString();
            } else if ($time === 'threeMonth') {
                $timeDate = $latestDate->subMonth(3)->toDateString();
            } else if ($time === 'sixMonth') {
                $timeDate = $latestDate->subMonth(6)->toDateString();
            } else if ($time === 'year') {
                $timeDate = $latestDate->subYear()->toDateString();
            } else if ($time === 'threeYear') {
                $timeDate = $latestDate->subYear(3)->toDateString();
            } else if ($time === 'fiveYear') {
                $timeDate = $latestDate->subYear(5)->toDateString();
            } else if ($time === 'tenYear') {
                $timeDate = $latestDate->subYear(10)->toDateString();
            } else if ($time === 'fifteenYear') {
                $timeDate = $latestDate->subYear(15)->toDateString();
            } else if ($time === 'twentyYear') {
                $timeDate = $latestDate->subYear(20)->toDateString();
            } else if ($time === 'twentyFiveYear') {
                $timeDate = $latestDate->subYear(25)->toDateString();
            } else if ($time === 'thirtyYear') {
                $timeDate = $latestDate->subYear(30)->toDateString();
            }

            if (isset($chunks['navs_chunks']['all'][$timeDate])) {
                $timeDateKey = array_search($timeDate, $datesKeys);
                $timeDateChunks = array_slice($chunks['navs_chunks']['all'], $timeDateKey);

                if (count($timeDateChunks) > 0) {
                    $chunks['navs_chunks'][$time] = [];

                    foreach ($timeDateChunks as $timeDateChunkDate => $timeDateChunk) {
                        $chunks['navs_chunks'][$time][$timeDateChunkDate] = [];
                        $chunks['navs_chunks'][$time][$timeDateChunkDate]['date'] = $timeDateChunk['date'];
                        $chunks['navs_chunks'][$time][$timeDateChunkDate]['nav'] = $timeDateChunk['nav'];
                        $chunks['navs_chunks'][$time][$timeDateChunkDate]['diff'] =
                            numberFormatPrecision($timeDateChunk['nav'] - $this->helper->first($timeDateChunks)['nav'], 4);
                        $chunks['navs_chunks'][$time][$timeDateChunkDate]['diff_percent'] =
                            numberFormatPrecision(($timeDateChunk['nav'] * 100 / $this->helper->first($timeDateChunks)['nav'] - 100), 2);
                    }
                }
            }
        }

        try {
            $this->localContent->write('.ff/sp/apps_fintech_mf_schemes_custom_chunks/data/' . $chunks['id'] . '.json', $this->helper->encode($chunks));
        } catch (FilesystemException | UnableToWriteFile | \throwable $e) {
            $this->addResponse($e->getMessage(), 1);

            return false;
        }

        //RollingReturns
        $rr = [];
        $rr['id'] = (int) $scheme['id'];
        $rr['last_updated'] = $this->helper->lastKey($customNavs);

        $latestDate = \Carbon\Carbon::parse($this->helper->lastKey($customNavs));
        $yearBefore = $latestDate->subYear()->toDateString();
        if (!isset($customNavs[$yearBefore])) {
            try {
                $this->localContent->write('.ff/sp/apps_fintech_mf_schemes_custom_rolling_returns/data/' . $rr['id'] . '.json', $this->helper->encode($rr));
            } catch (FilesystemException | UnableToWriteFile | \throwable $e) {
                $this->addResponse($e->getMessage(), 1);

                return false;
            }

            return true;
        }

        $processingYear = null;

        foreach ($customNavs as $date => $nav) {
            foreach (['year', 'two_year', 'three_year', 'five_year', 'seven_year', 'ten_year', 'fifteen_year', 'twenty_year', 'twenty_five_year'] as $rrTerm) {
                try {
                    $fromDate = \Carbon\Carbon::parse($date);

                    if ($fromDate->isWeekend()) {
                        continue;
                    }

                    if (!$processingYear) {
                        $processingYear = $fromDate->year;
                    }

                    if ($processingYear !== $fromDate->year) {
                        $processingYear = $fromDate->year;
                    }

                    $time = null;

                    if ($rrTerm === 'year') {
                        $toDate = $fromDate->addYear()->toDateString();
                        $time = 1;
                    } else if ($rrTerm === 'two_year') {
                        $toDate = $fromDate->addYear(2)->toDateString();
                        $time = 2;
                    } else if ($rrTerm === 'three_year') {
                        $toDate = $fromDate->addYear(3)->toDateString();
                        $time = 3;
                    } else if ($rrTerm === 'five_year') {
                        $toDate = $fromDate->addYear(5)->toDateString();
                        $time = 5;
                    } else if ($rrTerm === 'seven_year') {
                        $toDate = $fromDate->addYear(7)->toDateString();
                        $time = 7;
                    } else if ($rrTerm === 'ten_year') {
                        $toDate = $fromDate->addYear(10)->toDateString();
                        $time = 10;
                    } else if ($rrTerm === 'fifteen_year') {
                        $toDate = $fromDate->addYear(15)->toDateString();
                        $time = 15;
                    } else if ($rrTerm === 'twenty_year') {
                        $toDate = $fromDate->addYear(20)->toDateString();
                        $time = 20;
                    } else if ($rrTerm === 'twenty_five_year') {
                        $toDate = $fromDate->addYear(25)->toDateString();
                        $time = 25;
                    }

                    if (isset($rr[$rrTerm][$date])) {
                        continue;
                    }

                    if (isset($customNavs[$toDate])) {
                        if (!isset($rr[$rrTerm])) {
                            $rr[$rrTerm] = [];
                        }

                        $rr[$rrTerm][$date]['from'] = $date;
                        $rr[$rrTerm][$date]['to'] = $toDate;
                        $rr[$rrTerm][$date]['cagr'] =
                            numberFormatPrecision((pow(($customNavs[$toDate]['nav']/$nav['nav']),(1/$time)) - 1) * 100);
                    }
                } catch (\throwable $e) {
                    $this->addResponse($e->getMessage(), 1);

                    return false;
                }
            }
        }

        try {
            $this->localContent->write('.ff/sp/apps_fintech_mf_schemes_custom_rolling_returns/data/' . $rr['id'] . '.json', $this->helper->encode($rr));
        } catch (FilesystemException | UnableToWriteFile | \throwable $e) {
            $this->addResponse($e->getMessage(), 1);

            return false;
        }
    }
}