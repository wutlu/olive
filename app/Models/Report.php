<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'name'
    ];

    # sayfalar
    public function pages()
    {
        return $this->hasMany('App\Models\ReportPage', 'report_id', 'id')->orderBy('sort', 'asc');
    }

    # kullanıcı
    public function user()
    {
        return $this->hasOne('App\Models\User\User', 'id', 'user_id');
    }

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }

    # durum
    public function status()
    {
        return !User::where('report_id', $this->id)->exists();
    }
}
