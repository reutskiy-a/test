<?php

namespace App\Modules\Tz\Jobs;

use Faker\Factory as Faker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DatabaseCore\UserRepository;

class ProcessDealsJob implements ShouldQueue
{
    use Queueable;

    private const REQUEST_LIMIT = 50;
    private const RESPONSE_LIMIT = 50;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $taskId,
        public int $count,
        public string $memberId,
    ) {
        //
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(20);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        set_time_limit(600);

        $api = app(ApiClientBitrix24::class);
        $userRepository = app(UserRepository::class);
        $api->setCredentials($userRepository->getFirstAdminByMemberId($this->memberId));

        if ($this->count > 0) {
            $this->createDeals($api, $this->count);
        }

    }

    public function createDeals($api, $count)
    {
        $faker = Faker::create();
        $queries = [];

        for ($i = 0; $i < $count; $i++) {
            $queries[] = [
                'method' => 'crm.deal.add',
                'params' => [
                    'fields' => [
                        'TITLE' => 'СДЕЛКА: ' . $faker->words(3, true),
                        'OPPORTUNITY' => rand(100000, 1000000)
                    ]
                ]
            ];
        }

        $queryChunks = array_chunk($queries, self::REQUEST_LIMIT);

        $merge = [];
        $progressCount = 0;

        foreach ($queryChunks as $key) {

            $key = $this->addCompaniesToDealsChunk($api, $key);

            $batch = $api->callBatch($key);
            $merge = array_merge($merge, $batch['result']['result'], $batch['result']['result_error']);

            $progressCount += self::REQUEST_LIMIT;

            Cache::store('redis')->put("deals_process_{$this->taskId}", [
                'progress' => $progressCount,
                'count' => $this->count
            ], 110);
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

    }

    public function addCompaniesToDealsChunk($api, array $dealsChunk): array
    {
        $companies = $this->createCompanies($api, self::REQUEST_LIMIT);

        foreach ($dealsChunk as $key => $value) {
            $dealsChunk[$key]['params']['fields']['COMPANY_ID'] = $companies[$key];
        }

        Log::channel('test')->info('add company id', $dealsChunk);

        return $dealsChunk;
    }

    public function createCompanies($api, $count)
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

}
