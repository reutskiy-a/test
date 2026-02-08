<?php

declare(strict_types=1);

namespace App\Modules\Tz\Controllers;

use App\Modules\Tz\Jobs\ProcessDealsJob;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Services\Batch;

class AddDataController extends BaseController
{
    private const REQUEST_LIMIT = 50;
    private const RESPONSE_LIMIT = 50;

    public function index(ApiClientBitrix24 $api, UserRepository $userRepository)
    {
        set_time_limit(600);
        $api->setCredentials($userRepository->getFirstAdminByMemberId(config('app.b24_member_id')));

        $taskId = Str::uuid()->toString();

        $dealsAvailable = $this->getTotalDeals($api);
        $dealsNeeded = 2500;
        $count = $dealsNeeded - $dealsAvailable;

        // начальный прогресс
        Cache::store('redis')->put("deals_process_{$taskId}", [
            'progress' => 0,
            'count' => $count
        ], 110);

        ProcessDealsJob::dispatch($taskId, $count, config('app.b24_member_id'));

        return response([
            'task_id' => $taskId,
            'count' => $count,
            'status' => 'started'
        ], 200);

    }

    public function getTotalDeals(ApiClientBitrix24 $api): int
    {
        $result = $api->call('crm.deal.list');
        return (int) $result['total'];
    }

    /**
     * @return void
     *
     * стримим состояние выполнения ProcessDealsJob на фронт через redis
     */
    public function getProgress(): void
    {
        $taskId = request('task_id');

        if (!$taskId) {
            abort(400, 'task_id is required');
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Access-Control-Allow-Origin: https://bitrix24-front.reutskiy-a.ru');


        $getCache = Cache::store('redis')->get("deals_process_{$taskId}");
        $progress = $getCache['progress'];
        $count = $getCache['count'];

        if ($progress <= $count) {
            echo "data: " . json_encode(['progress' => $progress]) . "\n\n";

            ob_flush();
            flush();
        }

        while ($progress < $count) {
            $progress = Cache::store('redis')->get("deals_process_{$taskId}")['progress'];

            echo "data: " . json_encode(['progress' => $progress]) . "\n\n";

            ob_flush();
            flush();

            usleep(300000);
        }
    }

    public function test(ApiClientBitrix24 $api, UserRepository $userRepository)
    {
        set_time_limit(600);
        $api->setCredentials($userRepository->getFirstAdminByMemberId(config('app.b24_member_id')));

        s($api->call('crm.deal.update', [
            'ID' => 66797,
            'FIELDS' => [
                'COMPANY_ID' => 36895
            ]
        ]));



//        s($this->deleteAllDeals($api)); exit('deals is deleted');
//        $companies = $this->createCompanies($api, 100);
//        s($companies);

        exit;

        $dealsAvailable = $this->getTotalDeals($api);
        $dealsNeeded = 200;

        $count = $dealsNeeded - $dealsAvailable;

        if ($count < $dealsNeeded) {
            s($this->createDeals($api, $count));
        }

        s('ok');
    }


    public function createDeals(ApiClientBitrix24 $api, $count)
    {
        $faker = Faker::create();
        $queries = [];

        for ($i = 0; $i < $count; $i++) {
            $queries[] = [
                'method' => 'crm.deal.add',
                'params' => [
                    'fields' => [
                        'TITLE' => $faker->words(3, true),
                        'OPPORTUNITY' => rand(100000, 1000000)
                    ]
                ]
            ];
        }

        $queryChunks = array_chunk($queries, self::REQUEST_LIMIT);

        $merge = [];
        foreach ($queryChunks as $key) {
            $batch = $api->callBatch($key);
            $merge = array_merge($merge, $batch['result']['result'], $batch['result']['result_error']);
        }

        $result = [];
        foreach ($merge as $key => $value) {

            if (is_bool($value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_int($value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_array($value) && key_exists('error', $value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_array($value) && !key_exists('error', $value)) {

                // для списочных методов
                // for list methods
                if (is_array(reset($value))
                    && is_string(array_key_first($value))
                    && key(reset($value)) === 0) {

                    foreach (reset($value) as $innerKey) {
                        $result = array_merge($result, [$innerKey]);
                    }
                    continue;
                }

                // для методов возвращающих массив данных обёрнутых в строковый ключ
                // for methods that return an array of data wrapped in a string key
                if (is_array(reset($value)) && is_string(array_key_first($value))) {
                    $result = array_merge($result, [reset($value)]);
                    continue;
                }

                // для методов возвращающих сразу массив данных
                // for methods returning an array of data directly
                if (!is_array(reset($value)) && is_string(array_key_first($value))) {
                    $result = array_merge($result, [$value]);
                }
            }
        }

        return $result;
    }

    public function createCompanies(ApiClientBitrix24 $api, $count)
    {
        $faker = Faker::create();
        $queries = [];

        for ($i = 0; $i < $count; $i++) {
            $queries[] = [
                'method' => 'crm.company.add',
                'params' => [
                    'fields' => [
                        'TITLE' => 'КОМПАНИЯ: ' . $faker->words(3, true),
                    ]
                ]
            ];
        }

        $queryChunks = array_chunk($queries, self::REQUEST_LIMIT);

        $merge = [];
        foreach ($queryChunks as $key) {
            $batch = $api->callBatch($key);
            $merge = array_merge($merge, $batch['result']['result'], $batch['result']['result_error']);
        }

        $result = [];
        foreach ($merge as $key => $value) {

            if (is_bool($value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_int($value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_array($value) && key_exists('error', $value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_array($value) && !key_exists('error', $value)) {

                // для списочных методов
                // for list methods
                if (is_array(reset($value))
                    && is_string(array_key_first($value))
                    && key(reset($value)) === 0) {

                    foreach (reset($value) as $innerKey) {
                        $result = array_merge($result, [$innerKey]);
                    }
                    continue;
                }

                // для методов возвращающих массив данных обёрнутых в строковый ключ
                // for methods that return an array of data wrapped in a string key
                if (is_array(reset($value)) && is_string(array_key_first($value))) {
                    $result = array_merge($result, [reset($value)]);
                    continue;
                }

                // для методов возвращающих сразу массив данных
                // for methods returning an array of data directly
                if (!is_array(reset($value)) && is_string(array_key_first($value))) {
                    $result = array_merge($result, [$value]);
                }
            }
        }

        return $result;
    }

    private function deleteAllDeals(ApiClientBitrix24 $api): bool
    {
        $batch = new Batch($api);
        $deals = $batch->getAll('crm.deal.list', ['select' => ['ID']]);

        $dealIds = array_column($deals, 'ID');

        $queries = [];
        foreach ($dealIds as $dealId) {
            $queries[] = [
                'method' => 'crm.deal.delete',
                'params' => [
                    'ID' => $dealId
                ]
            ];
        }

        $batch->call($queries);

        return true;
    }

}
