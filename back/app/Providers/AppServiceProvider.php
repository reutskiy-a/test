<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PDO;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ApiDatabaseConfig::class, function () {
            $pdo = new PDO(
                config('database.connections.mysql.driver')
                . ":host=" . config('database.connections.mysql.host')
                . ";port=" . config('database.connections.mysql.port')
                . ";dbname=" . config('database.connections.mysql.database')
                . ";charset=" . config('database.connections.mysql.charset'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password')
            );

            return ApiDatabaseConfig::build($pdo);
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(ApiDatabaseConfig::class));
        });

        $this->app->bind(ApiClientBitrix24::class, function ($app) {
            $databaseConfig = $app->make(ApiDatabaseConfig::class);
            $apiSettings = new ApiClientSettings(AuthType::TOKEN);

            $logger = new Logger('api-b24');
            $handler = new RotatingFileHandler(
                storage_path('logs/rest-api-bitrix24.log'),
                15,
                Logger::DEBUG
            );
            $formatter = new LineFormatter(
                "[%datetime%] %level_name%: %message% %context%\n",
                'Y-m-d H:i:s',
                true
            );
            $formatter->setJsonPrettyPrint(true);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return new ApiClientBitrix24($apiSettings, $databaseConfig, $logger);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
