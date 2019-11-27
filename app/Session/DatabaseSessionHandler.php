<?php

namespace App\Session;

use Request;

use Jenssegers\Agent\Agent;

class DatabaseSessionHandler extends \Illuminate\Session\DatabaseSessionHandler
{
    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        $user_id = auth()->check() ? auth()->user()->id : null;

        $agent = new Agent();

        $agent_browser = $agent->browser();
        $agent_platform = $agent->platform();

        $browser['name'] = $agent_browser ? $agent_browser : 'unknown';

        if ($agent->version($agent_browser))
        {
        	$browser['version'] = $agent->version($agent_browser);
        }

        $os['name'] = $agent_platform ? $agent_platform : 'unknown';

        if ($agent->version($agent_platform))
        {
        	$os['version'] = $agent->version($agent_platform);
        }

        $array = [
        	'ip_address' => Request::ip(),
        	'payload' => base64_encode($data),
            'user_id' => $user_id,
            'user_agent' => Request::server('HTTP_USER_AGENT'),
            'last_activity' => time(),

            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_desktop' => $agent->isDesktop(),
            'is_phone' => $agent->isPhone(),

            'device' => $agent->device() ? $agent->device() : null,
            'os' => json_encode($os),
            'browser' => json_encode($browser),

            'robot' => $agent->isRobot() ? $agent->robot() : null,
        ];

        if (Request::isMethod('get'))
        {
        	$array['page'] = str_limit(url()->full(), 255);
        }

        if ($this->exists)
        {
        	$session = (object) $this->getQuery()->find($sessionId);

        	if (Request::isMethod('get'))
        	{
        		$array['ping'] = $session->ping + 1;
        	}

            $this->getQuery()->where('id', $sessionId)->update($array);
        }
        else
        {
        	$array['id'] = $sessionId;
        	$array['ping'] = 1;

            $referer = Request::server('HTTP_REFERER');

            if ($referer)
            {
            	$array['referer'] = str_limit($referer, 255);
            }

            $this->getQuery()->insert($array);
        }

        $this->exists = true;
    }
}
