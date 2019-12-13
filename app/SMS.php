<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use SoapClient;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Term;
use System;

class SMS extends Model
{
    public static function send(string $message = null, array $gsmno = [], bool $brand = true)
    {
        $message = Term::convertAscii(
            $message,
            [
                'transliterate' => true
            ]
        );

        if (strlen($message) >= 160)
        {
            $result = [
                'status' => 'err',
                'message' => 'Mesaj içeriği 160 karakterden fazla.'
            ];
        }
        else if (strlen($message) == 0)
        {
            $result = [
                'status' => 'err',
                'message' => 'Mesaj içeriği boş.'
            ];
        }
        else if (count($gsmno) == 0)
        {
            $result = [
                'status' => 'err',
                'message' => 'En az 1 gsm numarası girmeniz gerekiyor.'
            ];
        }
        else
        {
            $arr = [ $message ];

            if ($brand)
            {
                $arr[] = 'Olive';
                $arr[] = config('app.url');
            }

            $message = implode('\n', $arr);

            $client = new Client();
            $response = $client->request('GET','https://api.netgsm.com.tr/sms/send/get/', [
                'query' => [
                    'usercode' => config('services.netgsm.usercode'),
                    'password' => config('services.netgsm.password'),
                    'gsmno' => implode(',', $gsmno),
                    'message' => $message,
                    'msgheader' => config('services.netgsm.msgheader'),
                ]
            ]);

            if ($response->getStatusCode() == 200)
            {
                $split = explode(' ', $response->getBody());
                $code = $split[0];

                switch ($code)
                {
                    case '01': $result = [ 'status' => 'err', 'message' => 'Mesaj tarihinde bir hata var. Gönderim NETGSM tarihi ile işleme alındı.' ]; break;
                    case '02': $result = [ 'status' => 'err', 'message' => 'Mesaj bitiş tarihinde bir hata var. Gönderim NETGSM tarihi ile işleme alındı.' ]; break;
                    case '20': $result = [ 'status' => 'err', 'message' => 'Mesajdaki ki problemden dolayı SMS gönderilemedi.' ]; break;
                    case '30': $result = [ 'status' => 'err', 'message' => 'Geçersiz kullanıcı adı, şifre, API veya IP kısıtlaması.' ]; break;
                    case '40': $result = [ 'status' => 'err', 'message' => 'Gönderici adı sistemde tanımlı değil.' ]; break;
                    case '70': $result = [ 'status' => 'err', 'message' => 'Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlarda eksik var.' ]; break;
                    default:
                        $result = [
                            'status' => 'ok',
                            'message' => 'SMS gönderimi başarılı.'
                        ];
                    break;
                }
            }
            else
            {
                $result = [
                    'status' => 'err',
                    'message' => 'NETGSM bağlantısı sağlanamadı.'
                ];
            }
        }

        if ($result['status'] == 'err')
        {
            System::log($result['message'], 'App\SMS::send('.json_encode($gsmno).')', 10);
        }

        return [
            'status' => $result['status'],
            'message' => $result['message']
        ];
    }
}
