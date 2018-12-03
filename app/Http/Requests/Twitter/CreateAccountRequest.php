<?php

namespace App\Http\Requests\Twitter;

use Illuminate\Foundation\Http\FormRequest;
use Validator;
use App\Models\Twitter\StreamingUsers;
use App\Console\Commands\Crawlers\Twitter\AccountControl;

class CreateAccountRequest extends FormRequest
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
            'limit' => 'Maksimum hesap limitine ulaştınız.',
            'twitter_account' => 'Hesap takip için uygun değil.'
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

        Validator::extend('twitter_account', function($attribute, $screen_name) use ($user) {
            try
            {
                $account = AccountControl::getUser([ 'screen_name' => $screen_name ]);

                session()->flash('account', $account);

                $stuser = StreamingUsers::where(
                    [
                        'organisation_id' => $user->organisation_id,
                        'user_id' => $account->id_str
                    ]
                )->exists();

                return $stuser ? false : true;
            }
            catch (\Exception $e)
            {
                return false;
            }
        });

        Validator::extend('limit', function($attribute) use ($user) {
            return count($user->organisation->streamingUsers) < $user->organisation->twitter_follow_limit_user;
        });

        return [
            'screen_name' => 'required|bail|string|max:48|limit|organisation_status|twitter_account'
        ];
    }
}
