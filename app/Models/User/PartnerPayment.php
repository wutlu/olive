<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class PartnerPayment extends Model
{
    protected $table = 'partner_payments';

    public function getProcessAttribute()
    {
       	if ($this->status == 'success')
       	{
       		if ($this->amount < 0)
       		{
       			$color = 'grey';
                $title = 'Çıkış Başarılı';
       		}
       		else if ($this->amount > 0)
       		{
       			$color = 'green';
                $title = 'Giriş Başarılı';
       		}
            else
            {
                $color = 'grey';
                $title = 'Sistem Dışı İşlem';
            }
       	}
        else if ($this->status == 'pending')
        {
            $color = 'blue';
            $title = 'Bekliyor...';
        }
        else if ($this->status == 'cancelled')
        {
            $color = 'red';
            $title = 'İptal Edildi';
        }

       	return [
            'color' => $color,
            'title' => $title
        ];
    }

    # user
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }
}
