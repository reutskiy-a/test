<?php

declare(strict_types=1);

namespace App\Modules\Tz\Controllers;

use App\Http\Controllers\Controller;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DatabaseCore\UserRepository;

class BaseController extends Controller
{
    protected ApiClientBitrix24 $api;
    protected UserRepository $userRepository;

    public function __construct(ApiClientBitrix24 $api, UserRepository $userRepository)
    {
        $api->setCredentials($userRepository->getFirstAdminByMemberId(config('app.b24_member_id')));
        $this->api = $api;
        $this->userRepository = $userRepository;
    }
}
