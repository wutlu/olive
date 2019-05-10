<?php

namespace App\Http\Requests\YouTube;

use Illuminate\Foundation\Http\FormRequest;

use Validator;

use App\Models\YouTube\FollowingVideos;

use YouTube;

class CreateVideoRequest extends FormRequest
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
            'limit' => 'Maksimum video limitine ulaştınız.',
            'youtube_video' => 'Video, takip için uygun değil.'
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

        Validator::extend('youtube_video', function($attribute, $video_url) use ($user) {
            try
            {
                $video_id = Youtube::parseVidFromURL($video_url);
                $video = Youtube::getVideoInfo($video_id);

                if (@$video)
                {
                    $stuser = FollowingVideos::where(
                        [
                            'organisation_id' => $user->organisation_id,
                            'video_id' => $video->id
                        ]
                    )->exists();

                    session()->flash('video', $video);

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
            return $user->organisation->youtubeFollowingVideos()->count() < $user->organisation->data_pool_youtube_video_limit;
        });

        return [
            'string' => 'required|bail|active_url|limit|youtube_video'
        ];
    }
}
