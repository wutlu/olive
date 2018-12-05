<?php

namespace App\Http\Requests\RealTime\PinGroup;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\RealTime\PinGroup;

use Validator;

use App\Http\Requests\IdRequest;

class PdfRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(IdRequest $request)
    {
        $id_rules = [
            'required',
            'integer'
        ];

        $user = auth()->user();

        $pg = PinGroup::where([
            'id' => $request->id,
            'organisation_id' => $user->organisation_id
        ])->first();

        if (@$pg)
        {
            if ($pg->html_to_pdf == 'process')
            {
                $id_rules[] = 'process_rule';
                Validator::extend('process_rule', false, 'Raporun hazırlanması henüz sürüyor.<br />Bittiğinde bir bildirim alacaksınız.');
            }
            else
            {
                $pins = count($pg->pins());

                if ($pins < 1)
                {
                    $id_rules[] = 'min_rule';
                    Validator::extend('min_rule', false, 'PDF alabilmek için grupta en az 1 pin olması gerekiyor.');
                }
                else if ($pins > 1000)
                {
                    $id_rules[] = 'max_rule';
                    Validator::extend('max_rule', false, 'PDF alabilmek için grupta en fazla 1 pin olması gerekiyor.');
                }
            }
        }
        else
        {
            $id_rules[] = 'nothing_rule';
            Validator::extend('nothing_rule', false, 'Pin grubuna artık ulaşılamıyor.');
        }

        return [
            'id' => $id_rules
        ];
    }
}
