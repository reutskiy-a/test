<?php

declare(strict_types=1);

namespace App\Modules\Tz\Controllers;

use App\Modules\Tz\Requests\DealUpdateRequest;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;

class EventUpdateCompanyController extends BaseController
{
    public function index()
    {
        echo 'test';
    }

    public function addUpdateEvent()
    {
        try {
            return $this->api->call('event.bind', [
                'EVENT' => 'ONCRMDEALUPDATE',
                'HANDLER' => config('app.url') . '/api/tz/event/handle-on-update',
                'EVENT_TYPE' => 'online'
            ]);
        } catch (Bitrix24ResponseException $exception) {
            return response($exception->getMessage(), 200);
        }
    }

    public function handleOnUpdateEvent(DealUpdateRequest $request)
    {
        $data = $request->validated();
        $dealId = $data['data']['FIELDS']['ID'];

        $deal = $this->api->call('crm.deal.get', ['id' => $dealId])['result'];
        $relatedCompanyId = $deal['COMPANY_ID'];

        $result = $this->addCommentToCompany(
            $relatedCompanyId,
            "Мы изменили эту сделку: <b>" . PHP_EOL . $deal['TITLE'] . "</b>"
        );
    }

    private function addCommentToCompany(int|string $id, string $text)
    {
        return $this->api->call('crm.timeline.comment.add', [
            'fields' => [
                'ENTITY_ID' => $id, // айди сделки
                'ENTITY_TYPE' => 'company',
                'COMMENT' => $text,
            ]
        ]);
    }


    public function getEventList()
    {
        return $this->api->call('event.get');
    }

    public function deleteEvent()
    {
        return $this->api->call('event.unbind',
            [
                'EVENT' => 'ONCRMDEALUPDATE',
                'HANDLER' => config('app.url') . '/api/tz/event/handle-on-update',
            ]
        );
    }
}
