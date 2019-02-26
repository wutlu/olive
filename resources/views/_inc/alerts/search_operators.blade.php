@push('local.scripts')
    $(document).on('click', '[data-trigger=info]', function() {
        modal({
            'id': 'operators',
            'title': 'Arama İfadeleri',
            'body': $('<ul />', {
                'class': 'collection',
                'html': [
                    $('<li />', {
                        'class': 'collection-item',
                        'html': '<code class="red white-text">(ev && araba) || motosiklet</code> operatörü ile, içerisinde ev ve araba veya motosiklet geçen kelimeler aranır.'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': '<code class="red white-text">(millet meclisi) || (büyük patlama)</code> operatörü ile, içerisinde "millet meclisi" veya "büyük patlama" geçen içerikler aranır.'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': '<code class="red white-text">+türkiye</code> ve <code class="red white-text">-günaydın</code> operatörleriyle +içersin veya -içermesin şeklinde bir arama gerçekleştirebilirsiniz.'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': 'Twitter için, <code class="red white-text">user.screen_name:olivedotzone</code> veya <code class="red white-text">user.id:606582774</code> operatörlerini kullanabilirsiniz.'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': 'Standart kaynaklar için, <code class="red white-text">site_id:12</code> operatörünü kullanabilirsiniz. Kaynak numaralarına <a class="orange-text" href="{{ route('sources') }}">Kaynaklar</a> sayfasınan erişebilirsiniz.'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': 'Ürün aramalarında kullanabileceğiniz <code class="red white-text">price.amount:>5000000</code> operatörü ile >, =, < gibi fiyat araması yapabilirsiniz. 100 TL ile 200 TL arası fiyat için: <code class="red white-text">price.amount:(+>=100 +<200)</code>'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': '<code class="red white-text">?</code> operatörü ile farklı harfleri tamamlayabilirsiniz. Bkz: <code class="red white-text">cumhu?iyet</code>'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': '<code class="red white-text">*</code> operatörü ile kelimeleri tamamlayabilirsiniz. Bkz: <code class="red white-text">türkiye cumhuri*</code>'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': '<code class="red white-text">~</code> operatörü ile yazım hatalarını düzelterek arama yapabilirsiniz. <code class="red white-text">~1</code> veya <code class="red white-text">~2</code> gibi düzeltme oranını yükseltebilirsiniz. Bkz: <code class="red white-text">ankaar~</code>'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': 'Duygu analizinde 0.0 ile 1 arası derecelendirme yapıyoruz. Pozitif için <code class="red white-text">sentiment.pos</code>, negatif için <code class="red white-text">sentiment.neg</code> ve nötr için <code class="red white-text">sentiment.neu</code> operatörlerini, <code class="red white-text">sentiment.neg:>0.4</code> vb. şekilde kullanabilirsiniz.'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': 'YouTube yorumları için ilgilendiğiniz video id`sini kullanabilirsiniz. <code class="red white-text">video_id:47-rQKinD8Y</code>'
                    }),
                    $('<li />', {
                        'class': 'collection-item',
                        'html': 'Sözlük kullanıcıları için <code class="red white-text">author:"saulreaver"</code> operatörü kullanılabilir.'
                    })
                ]
            }),
            'size': 'modal-large',
            'options': {},
            'footer': [
                $('<a />', {
                    'href': '#',
                    'class': 'modal-close waves-effect btn-flat',
                    'html': buttons.ok
                })
            ]
        });
    })
@endpush
