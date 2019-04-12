<?php

namespace App\Http\Requests\YouTube;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\YouTube\FollowingChannels;

use YouTube;

class CreateChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'limit' => 'Maksimum kanal limitine ulaştınız.',
            'youtube_channel' => 'Kanal, takip için uygun değil.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();

        Validator::extend('youtube_channel', function($attribute, $channel_url) use ($user) {
            try
            {
                preg_match('/https:\/\/www\.youtube\.com\/(channel|user)?\/?([a-zA-Z0-9_-]+)/', $channel_url, $matches);

                switch (@$matches[1])
                {
                    case 'channel':
                        $channel = Youtube::getChannelById($matches[2]);
                    break;
                    case 'user':
                        $channel = Youtube::getChannelByName($matches[2]);
                    break;
                    default:
                        $channel = Youtube::getChannelByName($matches[2]);
                    break;
                }

                if (@$channel)
                {
                    $stuser = FollowingChannels::where(
                        [
                            'organisation_id' => $user->organisation_id,
                            'channel_id' => $channel->id
                        ]
                    )->exists();

                    session()->flash('channel', $channel);

                    return $stuser ? false : true;
                }
                else
                {
                    return false;
                }
            }
            catch (\Exception $e)
            {
                return false;
            }
        });

        Validator::extend('limit', function($attribute) use ($user) {
            return $user->organisation->youtubeFollowingChannels()->count() < $user->organisation->data_pool_youtube_channel_limit;
        });

        return [
            'channel_url' => 'required|bail|active_url|limit|youtube_channel'
        ];
    }
}
