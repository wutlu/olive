<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisTool extends Model
{
    protected $table = 'analysis_tools';

    public function link()
    {
        switch ($this->platform)
        {
            case 'twitter':
                return 'https://twitter.com/intent/user?user_id='.$this->social_id;
            break;
            case 'instagram':
                return 'https://www.instagram.com/'.$this->social_title.'/';
            break;
            case 'youtube':
                return 'https://www.youtube.com/channel/'.$this->social_id;
            break;
        }
    }
}
