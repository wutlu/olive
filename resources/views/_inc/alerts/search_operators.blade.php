@push('local.scripts')
    $(document).on('click', '[data-trigger=info]', function() {
        modal({
            'id': 'badge',
            'body': $('<ul />', {
                'html': [
                    $('<li />', {
                        'html': '- <span class="cyan-text">ev AND araba OR motosiklet</span> operatörü ile, içerisinde ev geçen veya araba ve motosiklet geçen kelimeler aranır.'
                    }),
                    $('<li />', {
                        'html': '- <span class="cyan-text">(millet meclisi) OR (büyük patlama)</span> operatörü ile, içerisinde tam manasıyla "millet meclisi" veya "büyük patlama" geçen içerikler aranır.'
                    }),
                    $('<li />', {
                        'html': '- Twitter için, <span class="cyan-text">user.screen_name:olivedotzone</span> veya <span class="cyan-text">user.id:606582774</span> operatörlerini kullanabilirsiniz.'
                    }),
                    $('<li />', {
                        'html': '- Standart kaynaklar için, <span class="cyan-text">site_id:12</span> operatörünü kullanabilirsiniz. Kaynak numaralarına <a class="orange-text" href="{{ route('sources') }}">Kaynaklar</a> sayfasınan erişebilirsiniz.'
                    })
                ]
            }),
            'size': 'modal-large',
            'options': {}
        });
    })
@endpush
