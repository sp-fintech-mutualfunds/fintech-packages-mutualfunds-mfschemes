<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use Apps\Fintech\Packages\Mf\Amcs\Model\AppsFintechMfAmcs;
use Apps\Fintech\Packages\Mf\Categories\Model\AppsFintechMfCategories;
use Apps\Fintech\Packages\Mf\Navs\Model\AppsFintechMfNavs;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesDetails;
use System\Base\BaseModel;

class AppsFintechMfSchemes extends BaseModel
{
    protected $modelRelations = [];

    public $id;

    public $isin;

    public $isin_reinvest;

    public $amfi_code;

    public $vendor;

    public $name;

    public $scheme_type;

    public $expense_ratio_type;

    public $management_type;

    public $plan_type;

    public $category_id;

    public $amc_id;

    public function initialize()
    {
        $this->modelRelations['details']['relationObj'] = $this->hasOne(
            'amfi_code',
            AppsFintechMfSchemesDetails::class,
            'amfi_code',
            [
                'alias'         => 'details'
            ]
        );

        $this->modelRelations['navs']['relationObj'] = $this->hasOne(
            'amfi_code',
            AppsFintechMfNavs::class,
            'amfi_code',
            [
                'alias'         => 'navs'
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