<?php

namespace Apps\Fintech\Packages\Mf\Schemes;

use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemes;
use System\Base\BasePackage;

class MfSchemes extends BasePackage
{
    protected $modelToUse = AppsFintechMfSchemes::class;

    protected $packageName = 'mfschemes';

    public $mfschemes;

    public function getSchemeById(int $id)
    {
        $this->setFFRelations(true);

        $this->getFirst('id', $id);

        if ($this->model) {
            $scheme = $this->model->toArray();

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
        trace([$this->ffData]);
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
}