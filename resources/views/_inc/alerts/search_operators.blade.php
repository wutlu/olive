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
                    })
                ]
            }),
            'size': 'modal-small',
            'options': {}
        });
    })
@endpush
