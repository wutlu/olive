<?php

namespace App\Utilities;

use App\Models\User\UserActivity;

class UserActivityUtility
{
    # user activity
    public static function push(string $title, array $array = [])
    {
        $user_id = @$array['user_id'] ? $array['user_id'] : auth()->user()->id;

        $query = new UserActivity;

        if (@$array['key'])
        {
            $query = $query->firstOrNew([ 'key' => $array['key'] ]);
        }
        else
        {
        	$array['key'] = implode('-', [ $user_id, str_random(16), str_random(4), str_random(4), date('ymdhis') ]);
        }

        $query->key = $array['key'];

        $query->title = $title;

		if (@$array['icon'])
		{
			$query->icon = $array['icon'];
		}

        if (@$array['markdown'])
        {
            $query->markdown = $array['markdown'];
        }

        if (@$array['markdown_color'])
        {
            $query->markdown_color = $array['markdown_color'];
        }

		if (@$array['button'])
		{
			$query->button_type = $array['button']['type'];
			$query->button_method = $array['button']['method'];
			$query->button_action = $array['button']['action'];
            $query->button_class = $array['button']['class'];
			$query->button_text = $array['button']['text'];
		}

        $query->user_id = $user_id;
        $query->save();
    }
}
