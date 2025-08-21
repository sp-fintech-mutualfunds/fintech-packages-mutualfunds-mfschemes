<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Model;

use System\Base\BaseModel;

class AppsFintechMfSchemesSnapshots extends BaseModel
{
    public $id;

    public $snapshots;

    public $navs_chunks_ids;

    public $rolling_returns_ids;
}