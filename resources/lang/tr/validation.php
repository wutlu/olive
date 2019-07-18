<?php

$arr = [
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
    'after' => ':attribute, :date alanından daha eski bir tarih olmalıdır.',
    'after_or_equal' => ':attribute alanı :date alanından sonra veya eşit olmalıdır.',
    'alpha' => ':attribute sadece harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute sadece harfler, rakamlar ve tirelerden oluşmalıdır.',
    'alpha_num' => ':attribute sadece harfler ve rakamlar içermelidir.',
    'array' => ':attribute dizi olmalıdır.',
    'before' => ':attribute :date tarihinden daha eski bir tarih olmalıdır.',
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
    'filled' => ':attribute alanının doldurulması zorunludur!',
    'image' => ':attribute alanı resim dosyası olmalıdır.',
    'in' => ':attribute değeri geçersiz.',
    'in_array' => ':attribute alanı :other içinde mevcut değil.',
    'integer' => ':attribute tamsayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON değişkeni olmalıdır.',
    'max' => [
        'numeric' => ':attribute değeri en fazla :max olabilir.',
        'file' => ':attribute değeri :max kilobayt değerinden küçük olmalıdır.',
        'string' => ':attribute uzunluğu :max karakterden kısa olmalıdır.',
        'array' => ':attribute değeri :max adedinden az nesneye sahip olmalıdır.',
    ],
    'mimes' => ':attribute dosya biçimi :values olmalıdır.',
    'mimetypes' => ':attribute dosya biçimi :values olmalıdır.',
    'min' => [
        'numeric' => ':attribute değeri en az :min olabilir.',
        'file' => ':attribute değeri :min kilobayt değerinden büyük olmalıdır.',
        'string' => ':attribute uzunluğu :min karakterden uzun olmalıdır.',
        'array' => ':attribute en az :min nesneye sahip olmalıdır.',
    ],
    'not_in' => ':attribute geçerli değil.',
    'numeric' => ':attribute sayı olmalıdır.',
    'present' => ':attribute alanı mevcut olmalıdır.',
    'regex' => ':attribute biçimi geçersiz.',
    'required' => ':attribute değeri boş kalamaz!',
    'required_if' => ':attribute alanını boş bırakamazsınız.', // :attribute alanı, :other :value değerine sahip olduğunda zorunludur.
    'required_unless' => ':attribute alanını boş bırakamazsınız.', // :attribute alanı, :other alanı :value değerlerinden birine sahip olmadığında zorunludur.
    'required_with' => ':attribute alanı :values varken zorunludur.',
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
    'unique' => 'Bu :attribute daha önceden kayıt edilmiş.',
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
    'has_route' => 'Rota oluşturulamadı.',
    'token_check' => 'Token geçerli değil.',
    'organisation_status' => 'Organizasyonunuz henüz aktif değil.',
    'slug' => 'Slug alanı sadece a-z0-9 ve - karakterlerinden oluşabilir.',
    'except_list' => 'Bu kelimeyi kullanamazsınız.',
    'iban' => 'Iban numarası geçerli değil.',
    'root_password' => 'Root şifresi geçerli değil.',

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
        'name'                            => 'Ad',
        'lastname'                        => 'Soyad',
        'email'                           => 'E-posta',
        'email_login'                     => 'E-posta',
        'email_password'                  => 'E-posta',
        'password'                        => 'Şifre',
        'password_login'                  => 'Şifre',
        'terms'                           => 'Kurallar',
        'gRecaptchaResponse'              => 'Güvenlik Doğrulaması',
        'month'                           => 'Ay',
        'address'                         => 'Adres',
        'notes'                           => 'Notlar',
        'country_id'                      => 'Ülke',
        'state_id'                        => 'Şehir',
        'city'                            => 'İlçe',
        'postal_code'                     => 'Posta Kodu',
        'person_name'                     => 'Ad',
        'person_lastname'                 => 'Soyad',
        'person_tckn'                     => 'T.C.K.N.',
        'type'                            => 'Tür',
        'corporate_type'                  => 'İşletme Türü',
        'tckn_without'                    => 'T.C.K.N. Girmeyeceğim',
        'merchant_name'                   => 'Ticari Ünvan',
        'tax_number'                      => 'Vergi No',
        'tax_office'                      => 'Vergi Dairesi',
        'tos'                             => 'Kullanım Koşulları',
        'organisation_name'               => 'Organizasyon Adı',
        'leave_key'                       => 'Ayrılma Onayı',
        'delete_key'                      => 'Silme Onayı',
        'user_id'                         => 'Kullanıcı',
        'message'                         => 'Mesaj',
        'subject'                         => 'Konu',
        'file'                            => 'Dosya',
        'ticket_id'                       => 'Destek',
        'avatar'                          => 'Avatar',
        'notification'                    => 'Bildirim',
        'string'                          => 'Sorgu',
        'id'                              => 'Kimlik',
        'start_date'                      => 'Başlangıç Tarihi',
        'start_time'                      => 'Başlangıç Saati',
        'end_date'                        => 'Bitiş Tarihi',
        'end_time'                        => 'Bitiş Saati',
        'serial'                          => 'Seri',
        'no'                              => 'No',
        'key'                             => 'Anahtar',
        'rate'                            => 'Oran',
        'price'                           => 'Miktar',
        'count'                           => 'Adet',
        'first_day'                       => 'Başangıç Günü',
        'last_day'                        => 'Bitiş Günü',
        'body'                            => 'Gövde',
        'slug'                            => 'Slug',
        'title'                           => 'Başlık',
        'keyword'                         => 'Kelime',
        'keywords'                        => 'Anahtar Kelimeler',
        'description'                     => 'Açıklama',
        'link'                            => 'Bağlantı',
        'site'                            => 'Site',
        'base'                            => 'Temel',
        'pattern'                         => 'Desen',
        'url_pattern'                     => 'URL Deseni',
        'selector_title'                  => 'Başlık Seçicisi',
        'selector_breadcrumb'             => 'Mini Harita Seçicisi',
        'selector_description'            => 'Açıklama Seçicisi',
        'test_count'                      => 'Test Sayısı',
        'value'                           => 'Değer',
        'google_search_query'             => 'Google Arama Satırı',
        'google_max_page'                 => 'Google Sayfa Sayısı',
        'selector_categories'             => 'Kategori Seçicisi',
        'selector_address'                => 'Adres Seçicisi',
        'selector_ul'                     => 'Bilgi Grubu Seçicisi',
        'selector_ul_li'                  => 'Bilgi Grubu Satır Seçicisi',
        'selector_ul_li_key'              => 'Bilgi Grubu Satır Anahtarı Seçicisi',
        'selector_ul_li_val'              => 'Bilgi Grubu Satır Değeri Seçicisi',
        'selector_seller_name'            => 'Satıcı Adı Seçicisi',
        'selector_seller_phones'          => 'Satıcı Telefonu Seçicisi',
        'selector_price'                  => 'Ücret Seçicisi',
        'consumer_key'                    => 'Consumer Key',
        'consumer_secret'                 => 'Consumer Secret',
        'access_token'                    => 'Access Token',
        'access_token_secret'             => 'Access Token Secret',
        'off_limit'                       => 'Kapatma Limiti',
        'max_attempt'                     => 'Maksimum Deneme',
        'deep_try'                        => 'Derin Deneme',
        'proxy'                           => 'Vekil Sunucu',
        'min_health'                      => 'Yaşam Değeri',
        'reason'                          => 'Sebep',
        'keyword_group'                   => 'Kelime Grubu',
        'pin_group'                       => 'Pin Grubu',
        'keyword_group.*'                 => 'Kelime Grubu',
        'group_id'                        => 'Grup',
        'index'                           => 'Indeks',
        'comment'                         => 'Yorum',
        'button_action'                   => 'Aksiyon Adresi',
        'button_text'                     => 'Buton Yazısı',
        'visibility'                      => 'Görünürlük',
        'sort'                            => 'Sıra',
        'category_id'                     => 'Kategori',
        'thread_id'                       => 'Konu',
        'reply_id'                        => 'Cevap',
        'route'                           => 'Rota',
        'carousel'                        => 'Carousel',
        'modal'                           => 'Modal',
        'about'                           => 'Hakkında',
        'send_date'                       => 'Gönderilecek Tarih',
        'send_time'                       => 'Gönderilecek Saat',
        'email_list'                      => 'E-posta Listesi',
        'process'                         => 'İşleme Alma',
        'pid'                             => 'İşlem Kimliği',

        'channel_url'                     => 'Kanal Adresi',
        'video_url'                       => 'Video Adresi',

        'channel_id'                      => 'Kanal Kimliği',

        'iban'                            => 'IBAN',
        'iban_name'                       => 'Hesap Adı',

        'status_message'                  => 'Durum Mesajı',

        'module'                          => 'Modül',
        'modules'                         => 'Modüller',
        'modules.*'                       => 'Modül',

        'source'                          => 'Kaynak',
        'sources'                         => 'Kaynaklar',
        'sources.*'                       => 'Kaynak',

        'sentiment'                       => 'Duygu',
        'sentiment_pos'                   => 'Pozitif Duygu',
        'sentiment_neg'                   => 'Negatif Duygu',
        'sentiment_neu'                   => 'Nötr Duygu',
        'sentiment_hte'                   => 'Nefret Söylemi Duygusu',

        'consumer_que'                    => 'Soru Analizi',
        'consumer_req'                    => 'İstek Analizi',
        'consumer_cmp'                    => 'Şikayet Analizi',
        'consumer_nws'                    => 'Haber Analizi',

        'full_match'                      => 'Kelimesi Kelimesine',

        'weekdays'                        => 'Günler',
        'weekdays.*'                      => 'Gün',

        'interval'                        => 'Aralık',

        'text'                            => 'Sorgu',
        'user_ids'                        => 'Kullanıcılar',

        'hit'                             => 'Bildirim Sayısı',

        'skip'                            => 'Geç',
        'take'                            => 'Yakala',
        'cookie'                          => 'Çerez',
        'standard'                        => 'Standart',

        'value_login'                     => 'E-posta veya Kullanıcı Adı',

        'user_name'                       => 'Kullanıcı Adı',
        'screen_name'                     => 'Görünen Ad',
        'organisation_name'               => 'Organizasyon Adı',

        'user_capacity'                   => 'Kullanıcı Kapasitesi',

        'data_twitter'                    => 'Twitter Verileri',
        'data_sozluk'                     => 'Sözlük Verileri',
        'data_news'                       => 'Haber Verileri',
        'data_youtube_video'              => 'YouTube Video Verileri',
        'data_youtube_comment'            => 'YouTube Yorum Verileri',
        'data_shopping'                   => 'E-ticaret Verileri',

        'real_time_group_limit'           => 'Gerçek Zamanlı Kelime Grubu Limiti',
        'alarm_limit'                     => 'Alarm Limiti',
        'pin_group_limit'                 => 'Pin Grubu Limiti',
        'historical_days'                 => 'Geriye Dönük Arama Limiti',
        'saved_searches_limit'            => 'Arama Kaydetme Limiti',

        'data_pool_youtube_channel_limit'  => 'YouTube Kanal Limiti',
        'data_pool_youtube_video_limit'    => 'YouTube Video Limiti',
        'data_pool_youtube_keyword_limit'  => 'YouTube Kelime Limiti',
        'data_pool_twitter_keyword_limit'  => 'Twitter Kelime Limiti',
        'data_pool_twitter_user_limit'     => 'Twitter Kullanıcı Limiti',
        'data_pool_instagram_follow_limit' => 'Instagram Takip Limiti',

        'unit_price'                      => 'Birim Fiyat',

        'module_real_time'                => 'Gerçek Zamanlı Modülü',
        'module_search'                   => 'Arama Modülü',
        'module_trend'                    => 'Trend Modülü',
        'module_alarm'                    => 'Alarm Modülü',
        'module_pin'                      => 'Pin Modülü',
        'module_model'                    => 'Model Modülü',
        'module_forum'                    => 'Forum Modülü',

        'phone'                           => 'Telefon',

        'retweet'                         => 'ReTweet',
        'media'                           => 'Medya',

        'analysis'                        => 'Analiz',
        'group'                           => 'Grup',
        'testarea'                        => 'Test Alanı',
        'engine'                          => 'Motor',

        'gender'                          => 'Cinsiyet',
        'search_name'                     => 'Arama Adı',
        'reverse'                         => 'İlk İçerikler',

        'root_password'                   => 'Root Şifresi',
        'root'                            => 'Sistem Sorumlusu',
        'admin'                           => 'Yönetici',

        'status'                          => 'Durum',

        'eagle_percent'                   => 'Eagle Yüzdesi',
        'phoenix_percent'                 => 'Phoenix Yüzdesi',
        'gryphon_percent'                 => 'Gryphon Yüzdesi',
        'dragon_percent'                  => 'Dragon Yüzdesi',

        'discount_with_year'              => 'Yıllık Ödeme İndirimi',
        'amount'                          => 'Miktar',
        'index_name'                      => 'Index Adı',
    ],
];

/**
 * modules
 */
foreach (config('system.modules') as $key => $module)
{
    $arr['attributes']['data_'.$key] = $module;
}

return $arr;
