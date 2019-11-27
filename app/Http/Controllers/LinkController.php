<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Link;
use App\Models\LinkLog;

use Jenssegers\Agent\Agent;

class LinkController extends Controller
{
    public static function get(string $key, Request $request)
    {
        $request->validate(
            [
                'short' => 'nullable|string|max:155'
            ]
        );

        $agent = new Agent();

        $agent_browser = $agent->browser();
        $agent_platform = $agent->platform();

        $browser['name'] = $agent_browser;

        if ($agent->version($agent_browser))
        {
            $browser['version'] = $agent->version($agent_browser);
        }

        $os['name'] = $agent_platform;

        if ($agent->version($agent_platform))
        {
            $os['version'] = $agent->version($agent_platform);
        }

        $referer = $request->server('HTTP_REFERER');

        if ($referer)
        {
            $referer = str_limit($referer, 255);
        }

        $link = Link::where('key', $key)->firstOrFail();

        LinkLog::insert(
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->server('HTTP_USER_AGENT'),
                'referer' => $referer,

                'is_mobile' => $agent->isMobile(),
                'is_tablet' => $agent->isTablet(),
                'is_desktop' => $agent->isDesktop(),
                'is_phone' => $agent->isPhone(),

                'device' => $agent->device() ? $agent->device() : null,
                'os' => json_encode($os),
                'browser' => json_encode($browser),

                'robot' => $agent->isRobot() ? $agent->robot() : null,
                'link_id' => $link->id,
                'short' => $request->short,

                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        );

        return redirect($link->url);
    }
}
