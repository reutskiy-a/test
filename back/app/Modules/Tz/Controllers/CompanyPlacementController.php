<?php

declare(strict_types=1);

namespace App\Modules\Tz\Controllers;

use App\Modules\Tz\Requests\PlacementGetCompanyDealsRequest;
use App\Modules\Tz\Requests\PlacementRequest;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;
use SimpleApiBitrix24\Services\Batch;

class CompanyPlacementController extends BaseController
{
    public function getPlacementList()
    {
        s($this->api->call('placement.list'));
    }

    public function deletePlacement()
    {
        s($this->api->call('placement.unbind', [
            'PLACEMENT' => 'CRM_COMPANY_DETAIL_TAB',
            'HANDLER' => config('app.url') . '/api/tz/placement/handle',
        ]));
    }

    public function addPlacement()
    {
        try {
            return $this->api->call(
                'placement.bind',
                [
                    'PLACEMENT' => 'CRM_COMPANY_DETAIL_TAB',
                    'HANDLER' => config('app.url') . '/api/tz/placement/handle',
                    'OPTIONS' => [
                        'errorHandlerUrl' => config('app.url')
                    ],
                    'TITLE' => 'Сделки этой компании'
                ]
            );
        } catch (Bitrix24ResponseException $exception) {
            return response($exception->getMessage(), 200);
        }
    }

    public function placementHandler(PlacementRequest $request)
    {
        $data = $request->validated();

        $id = json_decode($data['PLACEMENT_OPTIONS'], true)['ID'];



        return redirect()->away(config('app.front_url') . '/placement/?id=' . $id);
    }

    public function getCompanyDeals(PlacementGetCompanyDealsRequest $request)
    {
        $companyId = $request->validated()['company_id'];

        $batch = new Batch($this->api);
        try {
            $deals = $batch->getAll('crm.deal.list',
                [
                    'filter' => ['COMPANY_ID' => $companyId],
                    'select' => ['ID', 'TITLE', 'OPPORTUNITY']
                ]
            );
        } catch (Bitrix24ResponseException $e) {
            return response($e->getMessage(), 200);
        }

        return response($deals, 200);
    }
}
