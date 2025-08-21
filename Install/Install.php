<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Install;

use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemes;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesAll;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesCustom;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesCustomChunks;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesCustomRollingReturns;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesNavs;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesNavsChunks;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesNavsRollingReturns;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesSnapshots;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesSnapshotsNavs;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesSnapshotsNavsChunks;
use Apps\Fintech\Packages\Mf\Schemes\Install\Schema\MfSchemesSnapshotsNavsRollingReturns;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemes;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesAll;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesCustom;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesCustomChunks;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesCustomRollingReturns;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesNavs;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesNavsChunks;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesNavsRollingReturns;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesSnapshots;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesSnapshotsNavs;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesSnapshotsNavsChunks;
use Apps\Fintech\Packages\Mf\Schemes\Model\AppsFintechMfSchemesSnapshotsNavsRollingReturns;
use System\Base\BasePackage;
use System\Base\Providers\ModulesServiceProvider\DbInstaller;

class Install extends BasePackage
{
    protected $databases;

    protected $dbInstaller;

    public function init()
    {
        $this->databases =
            [
                'apps_fintech_mf_schemes'  => [
                    'schema'        => new MfSchemes,
                    'model'         => new AppsFintechMfSchemes,
                    'configParams'  =>
                        [
                            'min_index_chars' => 6
                        ]
                ],
                'apps_fintech_mf_schemes_all'  => [
                    'schema'        => new MfSchemesAll,
                    'model'         => new AppsFintechMfSchemesAll,
                    'configParams'  =>
                        [
                            'min_index_chars' => 6
                        ]
                ],
                'apps_fintech_mf_schemes_navs'  => [
                    'schema'        => new MfSchemesNavs,
                    'model'         => new AppsFintechMfSchemesNavs,
                ],
                'apps_fintech_mf_schemes_navs_chunks'  => [
                    'schema'        => new MfSchemesNavsChunks,
                    'model'         => new AppsFintechMfSchemesNavsChunks,
                ],
                'apps_fintech_mf_schemes_navs_rolling_returns'  => [
                    'schema'        => new MfSchemesNavsRollingReturns,
                    'model'         => new AppsFintechMfSchemesNavsRollingReturns,
                ],
                'apps_fintech_mf_schemes_custom'  => [
                    'schema'        => new MfSchemesCustom,
                    'model'         => new AppsFintechMfSchemesCustom,
                ],
                'apps_fintech_mf_schemes_custom_chunks'  => [
                    'schema'        => new MfSchemesCustomChunks,
                    'model'         => new AppsFintechMfSchemesCustomChunks,
                ],
                'apps_fintech_mf_schemes_custom_rolling_returns'  => [
                    'schema'        => new MfSchemesCustomRollingReturns,
                    'model'         => new AppsFintechMfSchemesCustomRollingReturns,
                ],
                'apps_fintech_mf_schemes_snapshots'  => [
                    'schema'        => new MfSchemesSnapshots,
                    'model'         => new AppsFintechMfSchemesSnapshots
                ],
                // 'apps_fintech_mf_schemes_snapshots_navs'  => [
                //     'schema'        => new MfSchemesSnapshotsNavs,
                //     'model'         => new AppsFintechMfSchemesSnapshotsNavs,
                // ],
                'apps_fintech_mf_schemes_snapshots_navs_chunks'  => [
                    'schema'        => new MfSchemesSnapshotsNavsChunks,
                    'model'         => new AppsFintechMfSchemesSnapshotsNavsChunks,
                ],
                'apps_fintech_mf_schemes_snapshots_navs_rolling_returns'  => [
                    'schema'        => new MfSchemesSnapshotsNavsRollingReturns,
                    'model'         => new AppsFintechMfSchemesSnapshotsNavsRollingReturns,
                ],
                // 'apps_fintech_mf_schemes_details'  => [
                //     'schema'        => new MfSchemesDetails,
                //     'model'         => new AppsFintechMfSchemesDetails,
                //     'configParams'  =>
                //         [
                //             'min_index_chars' => 3
                //         ]
                // ]
            ];

        $this->dbInstaller = new DbInstaller;

        return $this;
    }

    public function install()
    {
        $this->preInstall();

        $this->installDb();

        $this->postInstall();

        return true;
    }

    protected function preInstall()
    {
        return true;
    }

    public function installDb()
    {
        $this->dbInstaller->installDb($this->databases);

        return true;
    }

    public function postInstall()
    {
        //Do anything after installation.
        return true;
    }

    public function truncate()
    {
        $this->dbInstaller->truncate($this->databases);
    }

    public function uninstall($remove = false)
    {
        if ($remove) {
            //Check Relationship
            //Drop Table(s)
            $this->dbInstaller->uninstallDb($this->databases);
        }

        return true;
    }
}