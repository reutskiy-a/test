<?php

declare(strict_types=1);

namespace App\Modules\Tz\Controllers;

use App\Modules\Tz\Requests\UserfieldRequest;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;
use SimpleApiBitrix24\Services\Batch;

class UserFieldController extends BaseController
{
    private const TYPE_NAME = 'my_type';
    private const FIELD_NAME = 'deals_report';
    private const HUMAN_TITLE = 'Отчет по сделкам';


    public function index()
    {
        try {
            $this->addUserProperty();
        } catch (Bitrix24ResponseException $e) {

        }

        try {
            return $this->addUserField();
        } catch (Bitrix24ResponseException $e) {
            return response($e->getMessage(), 200);
        }
    }

    public function delete()
    {
        $this->deleteUserfield();
        $this->deleteUserProperty();
    }



    public function handle(UserfieldRequest $request)
    {
        $data = $request->validated();
        $placement = json_decode($data['PLACEMENT_OPTIONS'], true);
        $companyId = $placement['ENTITY_VALUE_ID'];

        $batch = new Batch($this->api);
        $deals = $batch->getAll('crm.deal.list', [
            'filter' => ['COMPANY_ID' => $companyId],
            'select' => ['ID', 'TITLE', 'OPPORTUNITY']
        ]);


        $dealsCount = count($deals);
        $dealsSum = array_sum(array_column($deals, 'OPPORTUNITY'));


        echo "
            Общее количество сделок: $dealsCount <br>
            Общая сумма сделок: $dealsSum руб.
        ";
    }

    private function addUserProperty(): array
    {
        try {
            return $this->api->call('userfieldtype.add', [
                'USER_TYPE_ID' => self::TYPE_NAME,
                'HANDLER' => config('app.url') . '/api/tz/userfield/handle',
                'TITLE' => 'MY CUSTOM TYPE'
            ]);
        } catch (Bitrix24ResponseException $exception) {
            return json_decode($exception->getMessage(), true);
        }
    }

    private function deleteUserProperty(): array
    {
        try {
            return $this->api->call('userfieldtype.delete', [
                'USER_TYPE_ID' => self::TYPE_NAME
            ]);
        } catch (Bitrix24ResponseException $exception) {
            return json_decode($exception->getMessage(), true);
        }
    }

    private function addUserField(): array
    {
        try {
            return $this->api->call('crm.company.userfield.add',
                [
                    'fields' => [
                        'USER_TYPE_ID' => self::TYPE_NAME,
                        'FIELD_NAME' => self::FIELD_NAME,
                        'XML_ID' => $this->randomString(10),
                        'MANDATORY' => 'N',
                        'SHOW_IN_LIST' => 'Y',
                        'EDIT_IN_LIST' => 'Y',
                        'EDIT_FORM_LABEL' => self::HUMAN_TITLE,
                        'LIST_COLUMN_LABEL' => self::HUMAN_TITLE,
                        'SETTINGS' => []
                    ]
                ]
            );
        } catch (Bitrix24ResponseException $exception) {
            return json_decode($exception->getMessage(), true);
        }
    }

    public function deleteUserfield(): array
    {
        $fields = $this->api->call('crm.company.userfield.list')['result'];

        $userFieldId = null;

        foreach ($fields as $field) {
            if ($field['USER_TYPE_ID'] == self::TYPE_NAME) {
                $userFieldId = $field['ID'];
                break;
            }
        }

        try {
            return $this->api->call('crm.company.userfield.delete', ['id' => $userFieldId]);
        } catch (Bitrix24ResponseException $e) {
            return json_decode($e->getMessage(), true);
        }
    }

    private function randomString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = mt_rand(0, strlen($characters) - 1);
            $result .= $characters[$randomIndex];
        }

        return $result;
    }

}
