<?php

namespace Apps\Fintech\Packages\Mf\Schemes;

use System\Base\BasePackage;

class MfSchemes extends BasePackage
{
    //protected $modelToUse = ::class;

    protected $packageName = 'mfschemes';

    public $mfschemes;

    public function getMfSchemesById($id)
    {
        $mfschemes = $this->getById($id);

        if ($mfschemes) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
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