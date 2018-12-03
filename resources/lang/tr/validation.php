<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => ':attribute kabul edilmelidir.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'after' => ':attribute şundan daha eski bir tarih olmalıdır :date.',
    'after_or_equal' => ':attribute tarihi :date tarihinden sonra veya tarihine eşit olmalıdır.',
    'alpha' => ':attribute sadece harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute sadece harfler, rakamlar ve tirelerden oluşmalıdır.',
    'alpha_num' => ':attribute sadece harfler ve rakamlar içermelidir.',
    'array' => ':attribute dizi olmalıdır.',
    'before' => ':attribute şundan daha önceki bir tarih olmalıdır :date.',
    'before_or_equal' => ':attribute tarihi :date tarihinden önce veya tarihine eşit olmalıdır.',
    'between' => [
        'numeric' => ':attribute :min - :max arasında olmalıdır.',
        'file' => ':attribute :min - :max arasındaki kilobayt değeri olmalıdır.',
        'string' => ':attribute :min - :max arasında karakterden oluşmalıdır.',
        'array' => ':attribute :min - :max arasında nesneye sahip olmalıdır.',
    ],
    'boolean' => ':attribute sadece doğru veya yanlış olmalıdır.',
    'confirmed' => ':attribute tekrarı eşleşmiyor.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_format' => ':attribute :format biçimi ile eşleşmiyor.',
    'different' => ':attribute ile :other birbirinden farklı olmalıdır.',
    'digits' => ':attribute :digits rakam olmalıdır.',
    'digits_between' => ':attribute :min ile :max arasında rakam olmalıdır.',
    'dimensions' => ':attribute görsel ölçüleri geçersiz.',
    'distinct' => ':attribute alanı yinelenen bir değere sahip.',
    'email' => ':attribute biçimi geçersiz.',
    'exists' => ':attribute sistemde kayıtlı değil.',
    'file' => ':attribute dosya olmalıdır.',
    'filled' => ':attribute alanının doldurulması zorunludur.',
    'image' => ':attribute alanı resim dosyası olmalıdır.',
    'in' => ':attribute değeri geçersiz.',
    'in_array' => ':attribute alanı :other içinde mevcut değil.',
    'integer' => ':attribute tamsayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON değişkeni olmalıdır.',
    'max' => [
        'numeric' => ':attribute değeri :max değerinden küçük olmalıdır.',
        'file' => ':attribute değeri :max kilobayt değerinden küçük olmalıdır.',
        'string' => ':attribute uzunluğu :max karakterden kısa olmalıdır.',
        'array' => ':attribute değeri :max adedinden az nesneye sahip olmalıdır.',
    ],
    'mimes' => ':attribute dosya biçimi :values olmalıdır.',
    'mimetypes' => ':attribute dosya biçimi :values olmalıdır.',
    'min' => [
        'numeric' => ':attribute değeri :min değerinden büyük olmalıdır.',
        'file' => ':attribute değeri :min kilobayt değerinden büyük olmalıdır.',
        'string' => ':attribute uzunluğu :min karakterden uzun olmalıdır.',
        'array' => ':attribute en az :min nesneye sahip olmalıdır.',
    ],
    'not_in' => ':attribute geçerli değil.',
    'numeric' => ':attribute sayı olmalıdır.',
    'present' => ':attribute alanı mevcut olmalıdır.',
    'regex' => ':attribute biçimi geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_if' => ':attribute alanını boş bırakamazsınız.', // :attribute alanı, :other :value değerine sahip olduğunda zorunludur.
    'required_unless' => ':attribute alanını boş bırakamazsınız.', // :attribute alanı, :other alanı :value değerlerinden birine sahip olmadığında zorunludur.
    'required_with' => ':attribute alanı zorunludur.', // :attribute alanı :values varken zorunludur.
    'required_with_all' => ':attribute alanı herhangi bir :values değeri varken zorunludur.',
    'required_without' => ':attribute alanını boş bırakamazsınız.', // :attribute alanı :values yokken zorunludur.
    'required_without_all' => ':attribute alanı :values değerlerinden herhangi biri yokken zorunludur.',
    'same' => ':attribute ile :other eşleşmelidir.',
    'size' => [
        'numeric' => ':attribute :size olmalıdır.',
        'file' => ':attribute :size kilobyte olmalıdır.',
        'string' => ':attribute :size karakter olmalıdır.',
        'array' => ':attribute :size nesneye sahip olmalıdır.',
    ],
    'string' => ':attribute dizge olmalıdır.',
    'timezone' => ':attribute geçerli bir saat dilimi olmalıdır.',
    'unique' => ':attribute daha önceden kayıt edilmiş.',
    'uploaded' => ':attribute yüklemesi başarısız.',
    'url' => ':attribute biçimi geçersiz.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'tckn' => ':attribute alanı geçerli değil.',
    'check_email_verification' => 'E-posta adresinizi doğrulamadan bu işlemi tamamlayamazsınız.',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    'authentication' => 'Hatalı şifre.',
    'password_check' => 'Şifre, hesabınızın şifresiyle uyuşmuyor.',
    'coupon_exists' => 'Kupon kodu geçersiz.',
    'has_route' => 'Rota oluşturulamadı.',
    'token_check' => 'Token geçerli değil.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name'                         => 'Ad',
        'lastname'                     => 'Soyad',
        'email'                        => 'E-posta',
        'email_login'                  => 'E-posta',
        'email_password'               => 'E-posta',
        'password'                     => 'Şifre',
        'password_login'               => 'Şifre',
        'terms'                        => 'Kurallar',
        'gRecaptchaResponse'           => 'Güvenlik Doğrulaması',
        'plan_id'                      => 'Plan',
        'month'                        => 'Ay',
        'coupon_code'                  => 'Kupon Kodu',
        'address'                      => 'Adres',
        'notes'                        => 'Notlar',
        'country_id'                   => 'Ülke',
        'state_id'                     => 'Şehir',
        'city'                         => 'İlçe',
        'postal_code'                  => 'Posta Kodu',
        'person_name'                  => 'Ad',
        'person_lastname'              => 'Soyad',
        'person_tckn'                  => 'T.C.K.N.',
        'type'                         => 'Tür',
        'corporate_type'               => 'İşletme Türü',
        'tckn_without'                 => 'T.C.K.N. Girmeyeceğim',
        'merchant_name'                => 'Ticari Ünvan',
        'tax_number'                   => 'Vergi No',
        'tax_office'                   => 'Vergi Dairesi',
        'tos'                          => 'Kullanım Koşulları',
        'organisation_name'            => 'Organizasyon Adı',
        'leave_key'                    => 'Ayrılma Onayı',
        'delete_key'                   => 'Silme Onayı',
        'user_id'                      => 'Kullanıcı',
        'message'                      => 'Mesaj',
        'subject'                      => 'Konu',
        'file'                         => 'Dosya',
        'ticket_id'                    => 'Destek',
        'avatar'                       => 'Avatar',
        'notification'                 => 'Bildirim',
        'string'                       => 'Kelime',
        'id'                           => 'Kimlik',
        'start_date'                   => 'Başlangıç Tarihi',
        'start_time'                   => 'Başlangıç Saati',
        'end_date'                     => 'Bitiş Tarihi',
        'end_time'                     => 'Bitiş Saati',
        'serial'                       => 'Seri',
        'no'                           => 'No',
        'key'                          => 'Anahtar',
        'rate'                         => 'Oran',
        'price'                        => 'Miktar',
        'discount_rate'                => 'İndirim Oranı',
        'discount_price'               => 'İndirim Miktarı',
        'count'                        => 'Adet',
        'first_day'                    => 'Başangıç Günü',
        'last_day'                     => 'Bitiş Günü',
        'body'                         => 'Gövde',
        'slug'                         => 'Slug',
        'title'                        => 'Başlık',
        'keyword'                      => 'Kelime',
        'keywords'                     => 'Anahtar Kelimeler',
        'description'                  => 'Açıklama',
        'link'                         => 'Bağlantı',
        'site'                         => 'Site',
        'base'                         => 'Temel',
        'pattern_url'                  => 'URL Deseni',
        'selector_title'               => 'Başlık Seçicisi',
        'selector_description'         => 'Açıklama Seçicisi',
        'test_count'                   => 'Test Sayısı',
        'value'                        => 'Değer',
        'google_search_query'          => 'Google Arama Satırı',
        'google_max_page'              => 'Google Sayfa Sayısı',
        'selector_categories'          => 'Kategori Seçicisi',
        'selector_address'             => 'Adres Seçicisi',
        'selector_ul'                  => 'Bilgi Grubu Seçicisi',
        'selector_ul_li'               => 'Bilgi Grubu Satır Seçicisi',
        'selector_ul_li_key'           => 'Bilgi Grubu Satır Anahtarı Seçicisi',
        'selector_ul_li_val'           => 'Bilgi Grubu Satır Değeri Seçicisi',
        'selector_seller_name'         => 'Satıcı Adı Seçicisi',
        'selector_seller_phones'       => 'Satıcı Telefonu Seçicisi',
        'selector_price'               => 'Ücret Seçicisi',
        'twitter_follow_limit_user'    => 'Kullanıcı Takip Limiti',
        'twitter_follow_limit_keyword' => 'Kelime Takip Limiti',
        'consumer_key'                 => 'Consumer Key',
        'consumer_secret'              => 'Consumer Secret',
        'access_token'                 => 'Access Token',
        'access_token_secret'          => 'Access Token Secret',
        'off_limit'                    => 'Kapatma Limiti',
        'max_attempt'                  => 'Maksimum Deneme',
        'deep_try'                     => 'Derin Deneme',
        'proxy'                        => 'Vekil Sunucu',
        'min_health'                   => 'Yaşam Değeri',
        'reason'                       => 'Neden',

        'module_youtube'               => 'YouTube Modülü',
        'module_twitter'               => 'Twitter Modülü',
        'module_sozluk'                => 'Sözlük Modülü',
        'module_news'                  => 'Haber Modülü',
        'module_shopping'              => 'Alışveriş Modülü',

        'keyword_group'                => 'Kelime Grubu',
        'keyword_group.*'              => 'Kelime Grubu',
    ],
];
