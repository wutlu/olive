<?php

namespace App\Http\Requests\Archive\Group;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Archive\Archive;

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

        $pg = Archive::where('id', $request->id)->first();

        if (@$pg)
        {
            if ($pg->html_to_pdf == 'process')
            {
                $id_rules[] = 'process_rule';

                Validator::extend('process_rule', false, 'Raporun hazırlanması henüz sürüyor.<br />Bittiğinde bir bildirim alacaksınız.');
            }
            else
            {
                $pins = $pg->pins()->count();

                if ($pins < 1)
                {
                    $id_rules[] = 'min_rule';

                    Validator::extend('min_rule', false, 'PDF alabilmek için grupta en az 1 pin olması gerekiyor.');
                }
                else if ($pins > 100)
                {
                    $id_rules[] = 'max_rule';

                    Validator::extend('max_rule', false, 'PDF alabilmek için grupta en fazla 100 pin olabilir.');
                }
            }
        }
        else
        {
            $id_rules[] = 'nothing_rule';

            Validator::extend('nothing_rule', false, 'Arşive artık ulaşılamıyor.');
        }

        return [
            'id' => $id_rules
        ];
    }
}
