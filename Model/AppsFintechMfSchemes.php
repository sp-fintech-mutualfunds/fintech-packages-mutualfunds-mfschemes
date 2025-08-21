<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use Apps\Fintech\Packages\Mf\Amcs\Model\AppsFintechMfAmcs;
use Apps\Fintech\Packages\Mf\Categories\Model\AppsFintechMfCategories;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesCustom;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesCustomChunks;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesCustomRollingReturns;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesDetails;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesNavs;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesNavsChunks;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesNavsRollingReturns;
use System\Base\BaseModel;

class AppsFintechMfSchemes extends BaseModel
{
    protected $modelRelations = [];

    public $id;

    public $isin;

    public $isin_reinvest;

    public $name;

    public $scheme_name;

    public $scheme_type;

    public $expense_ratio_type;

    public $management_type;

    public $plan_type;

    public $category_id;

    public $amc_id;

    public $closed;

    public $closed_date;

    public $minimum_amount;

    public $launch_date;

    public $start_date;

    public $crisil_rating;

    public $scheme_md5;

    public $navs_last_updated;

    public $day_trajectory;

    public $day_cagr;

    public $year_cagr;

    public $two_year_cagr;

    public $three_year_cagr;

    public $five_year_cagr;

    public $seven_year_cagr;

    public $ten_year_cagr;

    public $fifteen_year_cagr;

    public $twenty_year_cagr;

    public $twenty_five_year_cagr;

    public $year_rr;

    public $two_year_rr;

    public $three_year_rr;

    public $five_year_rr;

    public $seven_year_rr;

    public $ten_year_rr;

    public $fifteen_year_rr;

    public $twenty_year_rr;

    public $twenty_five_year_rr;

    public $latest_nav;

    public function initialize()
    {
        // $this->modelRelations['details']['relationObj'] = $this->hasOne(
        //     'id',
        //     AppsFintechMfSchemesDetails::class,
        //     'id',
        //     [
        //         'alias'         => 'details'
        //     ]
        // );

        $this->modelRelations['navs']['relationObj'] = $this->hasOne(
            'id',
            AppsFintechMfSchemesNavs::class,
            'id',
            [
                'alias'         => 'navs'
            ]
        );

        $this->modelRelations['navs_chunks']['relationObj'] = $this->hasOne(
            'id',
            AppsFintechMfSchemesNavsChunks::class,
            'id',
            [
                'alias'         => 'navs_chunks'
            ]
        );

        $this->modelRelations['rolling_returns']['relationObj'] = $this->hasOne(
            'id',
            AppsFintechMfSchemesNavsRollingReturns::class,
            'id',
            [
                'alias'         => 'rolling_returns'
            ]
        );

        $this->modelRelations['custom']['relationObj'] = $this->hasOne(
            'id',
            AppsFintechMfSchemesCustom::class,
            'id',
            [
                'alias'         => 'custom'
            ]
        );

        $this->modelRelations['custom_chunks']['relationObj'] = $this->hasOne(
            'id',
            AppsFintechMfSchemesCustomChunks::class,
            'id',
            [
                'alias'         => 'custom_chunks'
            ]
        );

        $this->modelRelations['custom_rolling_returns']['relationObj'] = $this->hasOne(
            'id',
            AppsFintechMfSchemesCustomRollingReturns::class,
            'id',
            [
                'alias'         => 'custom_rolling_returns'
            ]
        );

        $this->modelRelations['category']['relationObj'] = $this->hasOne(
            'category_id',
            AppsFintechMfCategories::class,
            'id',
            [
                'alias'         => 'category'
            ]
        );

        $this->modelRelations['amc']['relationObj'] = $this->hasOne(
            'amc_id',
            AppsFintechMfAmcs::class,
            'id',
            [
                'alias'         => 'amc'
            ]
        );

        parent::initialize();
    }

    public function getModelRelations()
    {
        if (count($this->modelRelations) === 0) {
            $this->initialize();
        }

        return $this->modelRelations;
    }
}