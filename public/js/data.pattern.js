/*!
 |-------------------------------
 | veri.zone 1.0
 |-------------------------------
 | (c) 2019 - veri.zone
 |-------------------------------
 */
$(document).on('click', '.read-aloud', function() {
    var __ = $(this);
    var text = __.closest('.data').find('.text-area').html();
        text = text.replace(/<\/?[^>]+(>|$)/g, '')
        text = text.replace('@', '')
        text = text.replace('#', '')
        text = encodeURI(text)

    if (__.hasClass('playing'))
    {
        $('.read-aloud').removeClass('playing').children('i.material-icons').html('volume_up')
        $.stopSound()
    }
    else
    {
        __.addClass('playing').children('i.material-icons').html('stop')
        $.playSound('https://tts.voicetech.yandex.net/tts?text=' + text + '&lang=tr_TR&format=mp3&platform=web&application=translate&chunked=0&mock-ranges=1')
    }
}).on('click', '[data-trigger=createAnalysis]', function() {
    var __ = $(this);

    var mdl = modal({
        'id': 'createAnalysis',
        'body': $('<form />', {
            'method': 'post',
            'action': '/analiz-araclari/analiz',
            'id': 'createAnalysis-form',
            'class': 'json',
            'data-callback': '__analysis_create',
            'html': [
                $('<input />', { 'type': 'hidden', 'name': 'id', 'value': __.data('id') }),
                $('<input />', { 'type': 'hidden', 'name': 'type', 'value': __.data('type') }),
                $('<input />', { 'type': 'hidden', 'name': 'index', 'value': __.data('index') }),
                $('<ul />', {
                    'class': 'collection collection-unstyled',
                    'html': [
                        $('<li />', {
                            'class': 'collection-item d-flex',
                            'html': $('<label />', {
                                'class': 'flex-fill align-self-center',
                                'html': [
                                    $('<input />', {
                                        'type': 'checkbox',
                                        'name': 'data_pool',
                                        'id': 'data_pool',
                                        'value': 'on'
                                    }),
                                    $('<span />', {
                                        'html': 'Kullanıcıyı Veri Havuzuna Ekle'
                                    })
                                ]
                            })
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex',
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center mr-1',
                                    'html': 'info'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center',
                                    'html': 'Oluşturacağınız analiz aracı, paylaşımı yapan kullanıcının profil değerlerini her gece kontrol eder.'
                                })
                            ]
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex',
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center mr-1 red-text',
                                    'html': 'warning'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center red-text',
                                    'html': 'Daha iyi sonuçlar alabilmek için, analiz alacağınız profilleri Veri Havuzuna da eklemelisiniz.'
                                })
                            ]
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex',
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center mr-1',
                                    'html': 'info'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center',
                                    'html': 'Takip ettiğiniz kullanıcıları Analiz Araçları sayfasından inceleyebilirsiniz.'
                                })
                            ]
                        })
                    ]
                })
            ]
        }),
        'size': 'modal-medium',
        'title': 'Analiz Aracı Oluştur',
        'options': {},
        'footer': [
            $('<a />', {
                'href': '#',
                'class': 'modal-close waves-effect btn-flat grey-text',
                'html': buttons.cancel
            }),
            $('<span />', {
                'html': ' '
            }),
            $('<button />', {
                'type': 'submit',
                'class': 'waves-effect btn-flat',
                'data-submit': 'form#createAnalysis-form',
                'html': buttons.ok
            })
        ]
    })
}).on('click', '[data-trigger=updateClass]', function() {
    var __ = $(this);

    var mdl = modal({
        'id': 'updateClass',
        'body': $('<form />', {
            'method': 'post',
            'action': '/db/siniflandir',
            'id': 'updateClass-form',
            'class': 'json',
            'data-callback': '__update_class',
            'html': [
                $('<input />', { 'type': 'hidden', 'name': 'id', 'value': __.data('id') }),
                $('<input />', { 'type': 'hidden', 'name': 'type', 'value': __.data('type') }),
                $('<input />', { 'type': 'hidden', 'name': 'index', 'value': __.data('index') }),
                $('<ul />', {
                    'class': 'collection collection-unstyled',
                    'html': [
                        $('<li />', {
                            'class': 'collection-item',
                            'css': { 'padding-top': 0, 'padding-bottom': 0 },
                            'html': $('<span />', { 'class': 'grey-text text-lighten-2', 'html': 'Duygu Analizi' })
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex flex-wrap',
                            'html': [
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'sentiment',
                                            'value': 'pos'
                                        }),
                                        $('<span />', {
                                            'html': 'Pozitif'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'sentiment',
                                            'value': 'neu'
                                        }),
                                        $('<span />', {
                                            'html': 'Nötr'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'sentiment',
                                            'value': 'neg'
                                        }),
                                        $('<span />', {
                                            'html': 'Negatif'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'sentiment',
                                            'value': 'hte'
                                        }),
                                        $('<span />', {
                                            'html': 'Nefret'
                                        })
                                    ]
                                })
                            ]
                        }),
                        $('<li />', {
                            'class': 'collection-item',
                            'css': { 'padding-top': 0, 'padding-bottom': 0 },
                            'html': $('<span />', { 'class': 'grey-text text-lighten-2', 'html': 'Müşteri Analizi' })
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex flex-wrap',
                            'html': [
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'consumer',
                                            'value': 'que'
                                        }),
                                        $('<span />', {
                                            'html': 'Soru'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'consumer',
                                            'value': 'req'
                                        }),
                                        $('<span />', {
                                            'html': 'İstek'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'consumer',
                                            'value': 'cmp'
                                        }),
                                        $('<span />', {
                                            'html': 'Şikayet'
                                        })
                                    ]
                                }),
                                $('<label />', {
                                    'class': 'flex-fill align-self-center',
                                    'css': { 'width': '50%' },
                                    'html': [
                                        $('<input />', {
                                            'type': 'radio',
                                            'name': 'consumer',
                                            'value': 'nws'
                                        }),
                                        $('<span />', {
                                            'html': 'Haber'
                                        })
                                    ]
                                })
                            ]
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex',
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center mr-1 red-text',
                                    'html': 'warning'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center red-text',
                                    'html': 'Bu içerik için mevcut sınıflama Olive tarafından oluşturulmuştur. Sınıflandırma doğru değilse, lütfen doğru seçeneği Olive\'e bildirin.'
                                })
                            ]
                        })
                    ]
                })
            ]
        }),
        'size': 'modal-medium',
        'title': 'Sınıflandır',
        'options': {},
        'footer': [
            $('<a />', {
                'href': '#',
                'class': 'modal-close waves-effect btn-flat grey-text',
                'html': buttons.cancel
            }),
            $('<span />', {
                'html': ' '
            }),
            $('<button />', {
                'type': 'submit',
                'class': 'waves-effect btn-flat',
                'data-submit': 'form#updateClass-form',
                'html': buttons.ok
            })
        ]
    })
})

function __analysis_create(__, obj)
{
    if (obj.status == 'ok')
    {
        $('#modal-createAnalysis').modal('close')

        M.toast({ html: 'Analiz Aracı Oluşturuldu', classes: 'green' })

        if (obj.data.data_pool == false)
        {
            M.toast({ html: 'Veri havuzunuz dolduğundan, seçtiğiniz hesap, veri havuzuna eklenemedi.', classes: 'red' })
        }
    }
}

function __update_class(__, obj)
{
    if (obj.status == 'ok')
    {
        $('#modal-updateClass').modal('close')

        return modal({
            'id': 'alert',
            'body': 'Veri gönderdiğiniz değerlere göre tekrar sınıflandırılacak.',
            'title': 'Teşekkürler',
            'size': 'modal-small',
            'options': {},
            'footer': [
               $('<a />', {
                   'href': '#',
                   'class': 'modal-close waves-effect btn-flat',
                   'html': buttons.ok
               })
            ]
        })
    }
}

function stacker(items, type)
{
    var sentiment_titles = {
        'pos': 'Pozitif',
        'neg': 'Negatif',
        'neu': 'Nötr',
        'hte': 'Nefret Söylemi'
    };

    var sentiment_class = {
        'pos': 'green',
        'neg': 'red',
        'neu': 'grey',
        'hte': 'black'
    };

    var consumer_titles = {
        'req': 'İSTEK',
        'que': 'SORU',
        'cmp': 'ŞİKAYET',
        'nws': 'HABER'
    };

    var consumer_class = {
        'req': '',
        'que': '',
        'cmp': '',
        'nws': ''
    };

    try
    {
        var _key = '';
        var _val = 0;

        $.each(items, function(key, val) {
            if (val >= _val)
            {
                _key = key;
                _val = val;
            }
        })

        _val = (_val * 100).toFixed(0);

        switch (type)
        {
            case 'sentiment':
                return (items.pos == 0.25 && items.neu == 0.25 && items.neg == 0.25 && items.hte == 0.25) ?
                {
                    'key': 'neu',
                    'val': 100,
                    'title': sentiment_titles['neu'],
                    'class': sentiment_class['neu']
                }
                :
                {
                    'key': _key,
                    'val': _val,
                    'title': sentiment_titles[_key],
                    'class': sentiment_class[_key]
                };
            break;
            case 'consumer':
                return (items.que == 0.25 && items.req == 0.25 && items.cmp == 0.25 && items.nws == 0.25) ?
                {
                    'key': null,
                    'val': null,
                    'title': null,
                    'class': 'hide'
                }
                :
                {
                    'key': _key,
                    'val': _val,
                    'title': consumer_titles[_key],
                    'class': consumer_class[_key]
                };
            break;
        }
    }
    catch (exception)
    {
        switch (type)
        {
            case 'sentiment':
                return {
                    'key': 'neu',
                    'val': 100,
                    'title': sentiment_titles['neu'],
                    'class': sentiment_class['neu']
                };
            break;
            case 'consumer':
                return {
                    'key': null,
                    'val': null,
                    'title': null,
                    'class': 'hide'
                };
            break;
        }
    }
}

function __joints(o)
{
    var _sentiment = stacker(o.sentiment, 'sentiment');
    var _consumer = stacker(o.consumer, 'consumer');

    var card = $('<div />', {
        'html': [
            $('<time>', {
                'html': o.created_at,
                'class': 'd-table mb-1'
            }),

            $('<div />', {
                'class': 'd-flex justify-content-between',
                'html': [
                    $('<div />', {
                        'class': 'align-self-center',
                        'html': [
                            $('<a />', {
                                'class': 'align-self-center btn-flat btn-floating waves-effect read-aloud',
                                'href': '#',
                                'html': $('<i />', {
                                    'class': 'material-icons',
                                    'html': 'volume_up',
                                    'css': { 'font-size': '24px' }
                                })
                            }),
                            $('<span />', { 'html': ' ' }),
                            $('<a />', {
                                'class': 'align-self-center btn-flat btn-floating waves-effect',
                                'href': '/db/' + o._index + '/' + o._type + '/' + o._id,
                                'html': $('<i />', {
                                    'class': 'material-icons',
                                    'html': 'info',
                                    'css': { 'font-size': '24px' }
                                })
                            }),
                            $('<span />', { 'html': ' ' }),
                            $('<a />', {
                                'href': '#',
                                'html': $('<i />', {
                                    'class': 'material-icons',
                                    'html': 'add',
                                    'css': { 'font-size': '24px' }
                                }),
                                'class': 'align-self-center btn-flat btn-floating waves-effect json',
                                'data-href': '/pinleme/add',
                                'data-method': 'post',
                                'data-include': 'group_id',
                                'data-callback': '__pin',
                                'data-error-callback': '__pin_dock',
                                'data-trigger': 'pin',
                                'data-id': o._id,
                                'data-pin-uuid': o.uuid,
                                'data-index': o._index,
                                'data-type': o._type
                            }).addClass($('[data-name=pin-dock-trigger]').length ? '' : 'hide'),
                            $('<span />', { 'html': ' ' }),
                            $('<a />', {
                                'class': 'align-self-center btn-flat btn-floating waves-effect hide',
                                'href': '#',
                                'data-trigger': 'createAnalysis',
                                'data-index': o._index,
                                'data-type': o._type,
                                'data-id': o._id,
                                'html': $('<i />', {
                                    'class': 'material-icons',
                                    'html': 'pie_chart',
                                    'css': { 'font-size': '24px' }
                                })
                            }).removeClass((o._type == 'tweet' || o._type == 'media' || o._type == 'video' || o._type == 'comment') ? 'hide' : '')
                        ]
                    }),
                    $('<div />', {
                        'class': 'd-flex align-self-center',
                        'html': [
                            $('<small />', {
                                'class': 'align-self-center',
                                'html': _consumer['title']
                            }).addClass(_consumer.class),
                            $('<a />', {
                                'class': 'btn-flat btn-small btn-floating waves-effect white-text align-self-center ml-1',
                                'html': $('<small />', { 'html': _sentiment.val + '%' }),
                                'data-trigger': 'updateClass',
                                'data-type': 'sentiment',
                                'data-index': o._index,
                                'data-type': o._type,
                                'data-id': o._id
                            }).addClass(_sentiment.class)
                        ]
                    })
                ]
            })
        ]
    })

    if (o.place)
    {
        card.prepend($('<a />', {
            'html': o.place.name,
            'href': '/arama-motoru?q=place.name:"' + o.place.name + '"'
        }))
    }

    if (o.deleted_at)
    {
        card.append($('<div />', {
            'class': 'd-flex justify-content-between red mt-1 p-1 z-depth-1',
            'html': [
                $('<span />', {
                    'html': 'Silindi',
                    'class': 'white-text'
                }),
                $('<span />', {
                    'html': o.deleted_at,
                    'class': 'white-text'
                })
            ]
        }))
    }

    return card;
}

function _tweet_(o)
{
    var tweet = $('<div />', {
        'class': 'data tweet',
        'html': [
            $('<div />', {
                'class': 'd-flex justify-content-between mb-1',
                'html': [
                    $('<div />', {
                        'class': 'align-self-center',
                        'html': [
                            $('<div />', {
                                'class': 'd-flex',
                                'html': [
                                    $('<img />', {
                                        'src': o.user.image,
                                        'alt': 'Avatar',
                                        'onerror': "this.onerror=null;this.src='/img/no_image-twitter.svg';",
                                        'css': {
                                            'width': '48px',
                                            'height': '48px'
                                        },
                                        'class': 'mr-1 tweet-avatar'
                                    }),
                                    $('<div />', {
                                        'class': 'align-self-center',
                                        'html': [
                                            $('<a />', {
                                                'html': o.user.name,
                                                'href': 'https://twitter.com/' + o.user.screen_name,
                                                'class': 'd-table red-text'
                                            }).attr('target', 'blank'),
                                            $('<a />', {
                                                'html': '@' + o.user.screen_name,
                                                'href': 'https://twitter.com/' + o.user.screen_name,
                                                'class': 'd-table grey-text'
                                            }).attr('target', 'blank')
                                        ]
                                    }),
                                    $('<i />', {
                                        'class': 'material-icons cyan-text hide ml-1',
                                        'html': 'check'
                                    }).removeClass(o.user.verified ? 'hide' : '')
                                ]
                            }),
                            $('<small />', {
                                'class': 'mt-1 grey-text',
                                'css': { 'max-width': '400px' },
                                'html': o.user.description
                            }).removeClass(o.user.description ? 'hide' : 'd-table').addClass(o.user.description ? 'd-table' : 'hide')
                        ]
                    }),
                    $('<div />', {
                        'class': 'align-self-end hide-on-med-and-down',
                        'html': [
                            $('<ul />', {
                                'class': 'd-flex justify-content-end m-0',
                                'html': [
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Tweet'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': number_format(o.user.counts.statuses)
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    }),
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Takipçi'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': number_format(o.user.counts.followers)
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    }),
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Takip'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': number_format(o.user.counts.friends)
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    })
                                ]
                            }),
                            $('<ul />', {
                                'class': 'm-0',
                                'html': [
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'ReTweet'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': o.counts.retweet ? number_format(o.counts.retweet) : 0
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    }),
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Favori'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': o.counts.favorite ? number_format(o.counts.favorite) : 0
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    }),
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Cevap'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': o.counts.reply ? number_format(o.counts.reply) : 0
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    }),
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Alıntı'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': o.counts.quote ? number_format(o.counts.quote) : 0
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    }),
                                ]
                            }).addClass(o.counts.retweet ? 'hide' : 'd-flex').addClass(o.counts.retweet ? 'd-flex' : 'hide')
                        ]
                    })
                ]
            }),
            $('<div />', {
                'class': 'media-area flex-wrap mb-1 hide'
            }),
            $('<div />', {
                'class': 'text-area',
                'html': o.text
            }),
            $('<a />', {
                'data-name': 'url',
                'html': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                'href': 'https://twitter.com/' + o.user.screen_name + '/status/' + o._id,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'class': 'mt-1',
                'html': __joints(o)
            })
        ]
    })

    if (o.medias)
    {
        $.each (o.medias, function (key, item) {
            if (item.media.type == 'photo')
            {
                var rid = Math.random();

                var img = $('<img />', {
                   'alt': 'Media',
                   'src': item.media.media_url,
                   'class': 'align-self-start z-depth-1',
                   'id': 'img-' + rid
                }).on('load', function() {
                    var __ = $(this);
                })

                tweet.find('.media-area').append(img).removeClass('hide').addClass('d-flex')
            }
            else if (item.media.type == 'video' || item.media.type == 'animated_gif')
            {
                tweet.find('.media-area').append($('<video />', {
                    'width': '100%',
                    'height': '100%',
                    'controls': 'true',
                    'html': [
                        $('<source />', {
                            'src': item.media.source_url,
                            'type': 'video/mp4'
                        }),
                        'Your browser does not support the video tag.'
                    ]
                })).removeClass('hide').addClass('d-flex')
            }
        })
    }

    if (o.illegal)
    {
        if (o.illegal.nud > 0.3)
        {
            tweet.find('.media-area').addClass('nude')
            tweet.find('.tweet-avatar').addClass('nude')
        }
    }

    return tweet;
}

function _media_(o)
{
    var media = $('<div />', {
        'class': 'data instagram',
        'html': [
            $('<div />', {
                'class': 'media-area mb-1',
                'html': $('<img />', {
                   'alt': 'Media',
                   'src': o.display_url,
                   'class': 'responsive-img z-depth-1'
                })
            })
        ]
    })

    if (o.text)
    {
        media.append($('<div />', {
            'html': o.text,
            'class': 'text-area'
        }))
    }

    if (o.illegal)
    {
        if (o.illegal.nud > 0.3)
        {
            media.find('.media-area').addClass('nude')
        }
    }

    media.append( $('<a />', {
        'data-name': 'url',
        'html': o.url,
        'href': o.url,
        'class': 'd-table green-text'
    }).attr('target', '_blank'))

    media.append($('<div />', {
        'html': __joints(o)
    }))

    return media;
}

function _entry_(o)
{
    var entry = $('<div />', {
        'class': 'data sozluk',
        'html': [
            $('<span />', {
                'html': o.title,
                'class': 'd-table title'
            }),
            $('<span />', {
                'html': o.author,
                'class': 'd-table red-text'
            }),
            $('<div />', {
                'html': o.text,
                'class': 'text-area'
            }),
            $('<a />', {
                'data-name': 'url',
                'html': o.url,
                'href': o.url,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': __joints(o)
            })
        ]
    })

    if (o.url.indexOf('eksisozluk.com') != -1)
    {
        entry.addClass('eksi')
    }
    else if (o.url.indexOf('incisozluk.com.tr') != -1)
    {
        entry.addClass('inci')
    }
    else if (o.url.indexOf('instela.com') != -1)
    {
        entry.addClass('instela')
    }
    else if (o.url.indexOf('uludagsozluk.com') != -1)
    {
        entry.addClass('uludag')
    }

    return entry;
}

function _article_(o)
{
    var article = $('<div />', {
        'class': 'data news',
        'html': [
            $('<div />', {
                'class': 'd-flex',
                'html': [
                    $('<span />', {
                        'data-name': 'avatar',
                        'class': 'align-self-center'
                    }),
                    $('<div />', {
                        'html': [
                            $('<span />', {
                                'html': o.title,
                                'class': 'd-table title'
                            }),
                            $('<div />', {
                                'html': o.text,
                                'class': 'text-area'
                            })
                        ]
                    })
                ]
            }),
            $('<a />', {
                'data-name': 'url',
                'html': str_limit(o.url, 96),
                'href': o.url,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': __joints(o)
            })
        ]
    })

    if (o.image)
    {
        article.find('[data-name=avatar]').html($('<img />', {
            'src': o.image,
            'alt': 'Image',
            'css': {
                'width': '96px',
                'min-width': '96px',
                'max-width': '96px',
                'max-height': '128px'
            },
            'class': 'mr-1',
            'onerror': "this.onerror=null;this.src='/img/no_image-article.svg';"
        }))
    }

    return article;
}

function _document_(o)
{
    var article_blog = $('<div />', {
        'class': 'data blog',
        'html': [
            $('<div />', {
                'class': 'd-flex',
                'html': [
                    $('<span />', {
                        'data-name': 'avatar',
                        'class': 'align-self-center'
                    }),
                    $('<div />', {
                        'html': [
                            $('<span />', {
                                'html': o.title,
                                'class': 'd-table title'
                            }),
                            $('<div />', {
                                'html': o.text,
                                'class': 'text-area'
                            })
                        ]
                    })
                ]
            }),
            $('<a />', {
                'data-name': 'url',
                'html': str_limit(o.url, 96),
                'href': o.url,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': __joints(o)
            })
        ]
    })

    if (o.image)
    {
        article_blog.find('[data-name=avatar]').html($('<img />', {
            'src': o.image,
            'alt': 'Image',
            'css': {
                'width': '96px',
                'min-width': '96px',
                'max-width': '96px',
                'max-height': '128px'
            },
            'class': 'mr-1',
            'onerror': "this.onerror=null;this.src='/img/no_image-article.svg';"
        }))
    }

    return article_blog;
}

function _product_(o)
{
    var product = $('<div />', {
        'class': 'data shopping',
        'html': [
            $('<span />', {
                'html': o.title,
                'class': 'd-table title'
            }),
            $('<div />', {
                'html': o.text ? o.text : 'Açıklama Yok',
                'class': 'text-area'
            }),
            $('<a />', {
                'data-name': 'url',
                'html': str_limit(o.url, 96),
                'href': o.url,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': __joints(o)
            })
        ]
    })

    if (o.url.indexOf('sahibinden.com') != -1)
    {
        product.addClass('sahibinden')
    }

    return product;
}

function _comment_(o)
{
    return $('<div />', {
        'class': 'data youtube comment',
        'html': [
            $('<a />', {
                'html': o.channel.title,
                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                'class': 'd-table red-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': o.text,
                'class': 'text-area text-area'
            }),
            $('<a />', {
                'data-name': 'url',
                'html': 'https://www.youtube.com/watch?v=' + o.video_id,
                'href': 'https://www.youtube.com/watch?v=' + o.video_id,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': __joints(o)
            })
        ]
    })
}

function _video_(o)
{
    return $('<div />', {
        'class': 'data youtube video',
        'html': [
            $('<div />', {
                'class': 'd-flex',
                'html': [
                    $('<img />', {
                        'src': 'https://i.ytimg.com/vi/' + o._id + '/hqdefault.jpg',
                        'alt': 'Image',
                        'css': {
                            'width': '96px',
                            'height': '54px'
                        },
                        'class': 'align-self-center mr-1'
                    }),
                    $('<div />', {
                        'class': 'align-self-center',
                        'html': [
                            $('<div />', {
                                'html': o.title,
                                'class': 'd-table title text-area'
                            }),
                            $('<a />', {
                                'html': o.channel.title,
                                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                                'class': 'd-table red-text'
                            }).attr('target', '_blank')
                        ]
                    })
                ]
            }),
            $('<a />', {
                'data-name': 'url',
                'html': 'https://www.youtube.com/watch?v=' + o._id,
                'href': 'https://www.youtube.com/watch?v=' + o._id,
                'class': 'd-table green-text'
            }).attr('target', '_blank'),
            $('<div />', {
                'html': __joints(o)
            })
        ]
    })
}
