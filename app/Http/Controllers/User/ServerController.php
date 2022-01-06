<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\ServerV2ray;
use App\Models\ServerLog;
use App\Models\User;

use App\Utils\Helper;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    public function fetch(Request $request)
    {
        $user = User::find($request->session()->get('id'));
        $servers = [];
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
        }
        return response([
            'data' => $servers
        ]);
    }

    public function getServerLogs(Request $request)
    {
        $serverLogModel = ServerLog::select([
            DB::raw('sum(u) as u'),
            DB::raw('sum(d) as d'),
            'log_at',
            'user_id',
            'rate'
        ])
            ->where('user_id', $request->session()->get('id'))
            ->where('log_at', '>=', strtotime(date('Y-m-1')))
            ->groupBy('log_at', 'user_id', 'rate')
            ->orderBy('log_at', 'DESC');
        return response([
            'data' => $serverLogModel->get()
        ]);
    }
}
