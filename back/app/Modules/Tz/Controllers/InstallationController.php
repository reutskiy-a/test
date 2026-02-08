<?php

declare(strict_types=1);

namespace App\Modules\Tz\Controllers;

use Illuminate\Http\Request;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\Services\Installation\InstallationService;

class InstallationController
{
    public function install(Request $request, ApiDatabaseConfig $databaseConfig, ApiClientBitrix24 $api)
    {
        $data = $request->toArray();

        $user = InstallationService::createUserFromProfileAndSave(
            $databaseConfig,
//            'local.6988761cd41400.00434578',
//            'e8IVhDgtjs5nc4Z8uQWvvamFxYz9MqBrZlNrhPGw58v4Pmxsku',
            'local.6983dd2b38eee3.35116309',
            'C2yOm2AkOVpkprMb3nnN1QnNH2gN6roKoujogdyFNevZGOxEFD',
            $data['member_id'],
            $data['AUTH_ID'],
            $data['REFRESH_ID'],
            $data['DOMAIN']
        );

        $api->setCredentials($user);

        InstallationService::finishInstallation();
    }

    public function createTable(ApiDatabaseConfig $databaseConfig)
    {
        $tableManager = new TableManager($databaseConfig);
//        return $tableManager->createUsersTableIfNotExists();
    }
}
