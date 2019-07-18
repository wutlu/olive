<?php

namespace App\Http\Requests\Instagram;

use Illuminate\Foundation\Http\FormRequest;

use Validator;
use System;

use App\Instagram;

class CreateUrlRequest extends FormRequest
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
            'limit' => 'Maksimum bağlantı limitine ulaştınız.',
            'private_unique' => 'Bu bağlantıyı zaten takip ediyorsunuz.',
            'connection' => 'Bağlantı geçerli değil!',
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

        Validator::extend('limit', function($attribute) use ($user) {
            return $user->organisation->instagramSelves()->count() < $user->organisation->data_pool_instagram_follow_limit;
        });

        Validator::extend('private_unique', function($attribute, $value) use ($user) {
            return $user->organisation->instagramSelves()->where('url', $value)->exists() ? false : true;
        });

        Validator::extend('connection', function($attribute, $value) {
        	// https://www.instagram.com/explore/tags/searching/
        	// https://www.instagram.com/explore/locations/212903416/istanbul-turkey/

        	if (strpos($value, '/explore/tags/'))
        	{
        		$method = 'hashtag';
        	}
        	else if (strpos($value, '/explore/locations/'))
        	{
        		$method = 'location';
        	}
        	else
        	{
        		$method = 'user';
        	}

            $instagram = new Instagram;
            $connect = $instagram->connect($value);

            if ($connect->status == 'ok')
            {
                $data = $instagram->data($method);

                if ($data->status == 'ok')
                {
                	session()->flash('method', $method);

                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        });

        return [
            'string' => 'required|bail|string|max:128|limit|private_unique|connection'
        ];
    }
}
