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
                            'class': 'collection-item',
                            'css': { 'padding-top': 0, 'padding-bottom': 0 },
                            'html': $('<span />', { 'class': 'grey-text text-lighten-2', 'html': 'Kategori' })
                        }),
                        $('<li />', {
                            'class': 'collection-item d-flex flex-wrap',
                            'data-name': 'change-categories'
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
                'html': keywords.cancel
            }),
            $('<span />', {
                'html': ' '
            }),
            $('<button />', {
                'type': 'submit',
                'class': 'waves-effect btn-flat',
                'data-submit': 'form#updateClass-form',
                'html': keywords.ok
            })
        ]
    })

    $.each(categories, function(key, item) {
        $('[data-name=change-categories]').append($('<label />', {
            'class': 'flex-fill align-self-center',
            'css': { 'width': '50%' },
            'html': [
                $('<input />', {
                    'type': 'radio',
                    'name': 'category',
                    'value': key
                }),
                $('<span />', {
                    'html': item.title
                })
            ]
        }))
    })

    return mdl;
})

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
                   'html': keywords.ok
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
                                'data-pin-uuid': o.uuid,
                                'data-id': o._id,
                                'data-type': o._type,
                                'data-index': o._index
                            }).addClass($('[data-name=pin-dock-trigger]').length ? '' : 'hide'),
                            $('<span />', { 'html': ' ' }),
                            $('<a />', {
                                'href': '#',
                                'html': $('<i />', {
                                    'class': 'material-icons',
                                    'html': 'note_add',
                                    'css': { 'font-size': '24px' }
                                }),
                                'class': 'align-self-center btn-flat btn-floating waves-effect json',
                                'data-href': '/db/data/' + o._index + '/' + o._type + '/' + o._id,
                                'data-index': o._index,
                                'data-type': o._type,
                                'data-id': o._id,
                                'data-method': 'post',
                                'data-callback': '__report__data'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'd-flex align-self-center',
                        'html': [
                            $('<small />', {
                                'class': 'align-self-center ml-1',
                                'html': o.category
                            }).addClass(o.category ? '' : 'hide'),
                            $('<small />', {
                                'class': 'align-self-center ml-1',
                                'html': _consumer['title']
                            }).addClass(_consumer.class),
                            $('<a />', {
                                'class': 'btn-flat btn-small btn-floating waves-effect white-text align-self-center ml-1',
                                'html': $('<small />', { 'html': _sentiment.val + '%' }),
                                'data-category': o.category,
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
                    'html': 'Ulaşılamıyor',
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

function _tweet_report_(o)
{
    var tweet = $('<div />', {
        'html': [
            $('<div />', {
                'class': 'd-flex justify-content-between',
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
                                        'class': 'align-self-center mr-1'
                                    }),
                                    $('<div />', {
                                        'class': 'align-self-center',
                                        'html': [
                                            $('<span />', { 'html': o.user.name, 'class': 'd-table red-text' }),
                                            $('<span />', { 'html': '@' + o.user.screen_name, 'class': 'd-table grey-text' })
                                        ]
                                    }),
                                    $('<i />', {
                                        'class': 'material-icons cyan-text hide ml-1',
                                        'html': 'check'
                                    }).removeClass(o.user.verified ? 'hide' : '')
                                ]
                            }),
                            $('<small />', {
                                'class': 'grey-text pt-1 pb-1',
                                'css': { 'max-width': '400px' },
                                'html': o.user.description
                            }).removeClass(o.user.description ? 'hide' : 'd-table').addClass(o.user.description ? 'd-table' : 'hide')
                        ]
                    }),
                    $('<div />', {
                        'class': 'align-self-end',
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
                'class': 'media-area flex-wrap hide'
            }),
            $('<div />', {
                'class': 'pt-1 pb-1',
                'html': o.text
            }),
            $('<div />', {
                'class': 'joints',
                'html': [
                    $('<span>', {
                        'html': o.created_at,
                        'class': 'date'
                    })
                ]
            })
        ]
    })

    if (o.place)
    {
        tweet.find('.joints').prepend($('<span />', { 'html': o.place.name }))
    }

    if (o.deleted_at)
    {
        tweet.find('.joints').append($('<span />', {
            'class': 'date red-text',
            'html': o.deleted_at + ' / Ulaşılamıyor'
        }))
    }

    if (o.entities)
    {
        if (o.entities.medias)
        {
            $.each (o.entities.medias, function (key, item) {
                if (item.media.type == 'photo')
                {
                    var rid = Math.random();

                    var img = $('<img />', {
                       'alt': 'Media',
                       'src': item.media.media_url,
                       'class': 'align-self-start',
                       'css': { 'height': '100px' },
                       'id': 'img-' + rid
                    }).on('load', function() {
                        var __ = $(this);
                    })

                    tweet.find('.media-area').append(img).removeClass('hide').addClass('d-flex')
                }
                else if (item.media.type == 'video' || item.media.type == 'animated_gif')
                {
                    tweet.find('.media-area').append($('<video />', {
                        'width': 'auto',
                        'height': '100px',
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
    }

    return tweet;
}

function __report__pattern(obj, form, type, method)
{
    switch (type)
    {
        case 'title':
        case 'lines':
            if (type == 'title' && method == 'read')
            {
                form.addClass('teal lighten-5')

                form.find('.logo').remove()
                form.find('.content').remove()
                form.find('.date').remove()
                form.find('.report-page').removeClass('report-page').addClass('content-title')
            }

            if (type == 'lines' || method == 'write')
            {
                form.find('.content').html(
                    [
                        $('<div />', {
                            'class': 'flex-fill lines',
                            'css': { 'width': '50%' }
                        }),
                        $('<div />', {
                            'class': 'flex-fill textarea markdown',
                            'css': { 'width': '50%' },
                            'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                        })
                    ]
                )

                form.find('.report-tools').prepend(
                    $('<a />', {
                        'href': '#',
                        'class': 'btn-floating btn-flat white waves-effect',
                        'data-report-element': 'line',
                        'html': $('<i />', {
                            'class': 'material-icons',
                            'html': 'text_format'
                        })
                    })
                )

                $.each(obj.data, function(key, o) {
                    var id = 'item-' + Math.floor(Math.random() * 1000000);

                    var element = $('<div />', {
                        'data-report-item': 'line',
                        'data-max-item': 10,
                        'class': 'draggable',
                        'id': id,
                        'css': {
                            'top': o.position.top,
                            'left': o.position.left,
                            'position': 'absolute'
                        },
                        'html': method == 'write' ? [
                            $('<i />', {
                                'class': 'material-icons drag-handle',
                                'html': 'drag_handle'
                            }),
                            $('<i />', {
                                'class': 'material-icons delete',
                                'html': 'delete',
                                'data-remove': '#' + id
                            }),
                            $('<input />', {
                                'placeholder': 'Satır',
                                'type': 'text',
                                'class': 'auto-width',
                                'maxlength': '50',
                                'value': o.text
                            })
                        ] : $('<div />', {
                            'class': 'd-flex',
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center',
                                    'html': 'navigate_next'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center',
                                    'html': o.text
                                })
                            ]   
                        })
                    });

                    form.find('.lines').append(element)
                })

                form.find('.lines').find('.draggable').draggable({
                    'handle': '.drag-handle',
                    'containment': '.lines',
                    'snap': true
                })
            }
        break;
        case 'article':
        case 'document':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            if (obj.data.image_url)
            {
                form.find('.report-data').append($('<img />', {
                    'class': 'thumb',
                    'alt': 'Resim',
                    'src': obj.data.image_url
                }))
            }

            form.find('.report-data').append($('<h4 />', { 'html': obj.data.title }))
            form.find('.report-data').append($('<p />', { 'html': obj.data.description }))
            form.find('.report-data').append($('<span />', {
                'class': 'date mt-1',
                'html': obj.data.created_at
            }))
        break;
        case 'entry':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            form.find('.report-data').append($('<h4 />', {
                'html': obj.data.title
            }))
            form.find('.report-data').append(
                $('<span />', {
                    'class': 'd-flex',
                    'html': [
                        $('<i />', {
                            'class': 'material-icons align-self-center mr-1',
                            'html': 'person'
                        }),
                        $('<span />', {
                            'class': 'align-self-center',
                            'html': obj.data.author
                        })
                    ]
                })
            )
            form.find('.report-data').append($('<p />', { 'html': obj.data.entry }))
            form.find('.report-data').append($('<span />', {
                'class': 'date mt-1',
                'html': obj.data.created_at
            }))
        break;
        case 'media':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            var media = $('<div />', {
                'html': $('<img />', {
                   'alt': 'Media',
                   'src': obj.data.display_url,
                   'css': { 'height': '100px' }
                })
            });

            if (obj.data.text)
            {
                media.append($('<div />', {
                    'html': obj.data.text,
                    'class': 'pt-1 pb-1'
                }))
            }

            media.append($('<div />', {
                'class': 'joints',
                'html': [
                    $('<span>', {
                        'html': obj.data.created_at,
                        'class': 'date'
                    })
                ]
            }))

            if (obj.data.place)
            {
                media.find('.joints').prepend($('<span />', { 'html': obj.data.place.name }))
            }

            if (obj.data.user)
            {
                if (obj.data.user.name)
                {
                    media.prepend($('<div />', {
                        'class': 'd-flex justify-content-between',
                        'html': [
                            $('<div />', {
                                'class': 'align-self-center',
                                'html': [
                                    $('<div />', {
                                        'class': 'd-flex',
                                        'html': [
                                            $('<img />', {
                                                'src': obj.data.user.image,
                                                'alt': 'Avatar',
                                                'onerror': "this.onerror=null;this.src='/img/no_image-twitter.svg';",
                                                'css': {
                                                    'width': '48px',
                                                    'height': '48px'
                                                },
                                                'class': 'align-self-center mr-1'
                                            }),
                                            $('<div />', {
                                                'class': 'align-self-center',
                                                'html': [
                                                    $('<span />', { 'html': obj.data.user.name, 'class': 'd-table red-text' }),
                                                    $('<span />', { 'html': '@' + obj.data.user.screen_name, 'class': 'd-table grey-text' })
                                                ]
                                            }),
                                            $('<i />', {
                                                'class': 'material-icons cyan-text hide ml-1',
                                                'html': 'check'
                                            }).removeClass(obj.data.user.verified ? 'hide' : '')
                                        ]
                                    }),
                                    $('<small />', {
                                        'class': 'grey-text pt-1 pb-1',
                                        'css': { 'max-width': '400px' },
                                        'html': obj.data.user.description
                                    }).removeClass(obj.data.user.description ? 'hide' : 'd-table').addClass(obj.data.user.description ? 'd-table' : 'hide')
                                ]
                            }),
                            $('<ul />', {
                                'class': 'd-flex align-self-start m-0',
                                'html': [
                                    $('<li />', {
                                        'html': [
                                            $('<small />', {
                                                'class': 'grey-text d-block right-align',
                                                'html': 'Medya'
                                            }),
                                            $('<span />', {
                                                'class': 'blue-grey-text d-block right-align',
                                                'html': number_format(obj.data.user.counts.media)
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
                                                'html': number_format(obj.data.user.counts.followed_by)
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
                                                'html': number_format(obj.data.user.counts.follow)
                                            })
                                        ],
                                        'css': { 'padding': '4px' }
                                    })
                                ]
                            })
                        ]
                    }))
                }
            }

            form.find('.report-data').append(media)
        break;
        case 'comment':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            form.find('.report-data').append(
                $('<div />', {
                    'class': 'card-panel',
                    'html': [
                        $('<a />', {
                            'class': 'link d-flex',
                            'target': '_blank',
                            'href': 'https://www.youtube.com/channel/' + obj.data.channel.id,
                            'html': [
                                $('<i />', {
                                    'class': 'material-icons align-self-center mr-1',
                                    'html': 'person'
                                }),
                                $('<span />', {
                                    'class': 'align-self-center',
                                    'html': obj.data.channel.title
                                })
                            ]
                        }),
                        $('<p />', {
                            'html': obj.data.text
                        }),
                        $('<span />', {
                            'class': 'date',
                            'html': obj.data.created_at
                        })
                    ]
                })
            )

            form.find('.report-data').append(
                $('<div />', {
                    'class': 'd-flex',
                    'html': [
                        $('<img />', {
                            'alt': 'Resim',
                            'src': 'https://i.ytimg.com/vi/' + obj.data.video.id + '/hq720.jpg',
                            'class': 'align-self-center thumb mr-1'
                        }),
                        $('<span />', {
                            'class': 'align-self-center',
                            'html': [
                                $('<h4 />', { 'html': obj.data.video.title }),
                                $('<a />', {
                                    'class': 'link d-flex',
                                    'target': '_blank',
                                    'href': 'https://www.youtube.com/channel/' + obj.data.video.channel.id,
                                    'html': [
                                        $('<i />', {
                                            'class': 'material-icons align-self-center mr-1',
                                            'html': 'play_circle_filled'
                                        }),
                                        $('<span />', {
                                            'class': 'align-self-center',
                                            'html': obj.data.video.channel.title
                                        })
                                    ]
                                })
                            ]
                        })
                    ]
                })
            )
        break;
        case 'video':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            form.find('.report-data').append(
                $('<div />', {
                    'class': 'd-flex',
                    'html': [
                        $('<img />', {
                            'alt': 'Resim',
                            'src': 'https://i.ytimg.com/vi/' + obj.data.id + '/hq720.jpg',
                            'class': 'align-self-center thumb mr-1'
                        }),
                        $('<span />', {
                            'class': 'align-self-center',
                            'html': [
                                $('<h4 />', { 'html': obj.data.title }),
                                $('<a />', {
                                    'class': 'link d-flex',
                                    'target': '_blank',
                                    'href': 'https://www.youtube.com/channel/' + obj.data.channel.id,
                                    'html': [
                                        $('<i />', {
                                            'class': 'material-icons align-self-center mr-1',
                                            'html': 'play_circle_filled'
                                        }),
                                        $('<span />', {
                                            'class': 'align-self-center',
                                            'html': obj.data.channel.title
                                        })
                                    ]
                                })
                            ]
                        })
                    ]
                })
            )

            if (obj.data.description)
            {
                form.find('.report-data').append($('<p />', {
                    'html': obj.data.description
                }))
            }

            form.find('.report-data').append($('<span />', {
                'class': 'date',
                'html': obj.data.created_at
            }))
        break;
        case 'product':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            form.find('.report-data').append($('<h4 />', {
                'html': obj.data.title
            }))

            if (obj.data.seller.name)
            {
                form.find('.report-data').append(
                    $('<span />', {
                        'class': 'd-flex',
                        'html': [
                            $('<i />', {
                                'class': 'material-icons align-self-center mr-1',
                                'html': 'person'
                            }),
                            $('<span />', {
                                'class': 'align-self-center',
                                'html': obj.data.seller.name
                            })
                        ]
                    })
                )
            }

            if (obj.data.description)
            {
                form.find('.report-data').append($('<p />', { 'html': obj.data.description }))
            }

            form.find('.report-data').append($('<span />', {
                'class': 'date mt-1',
                'html': obj.data.created_at
            }))
        break;
        case 'tweet':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-data',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            if (obj.data.original)
            {
                form.find('.report-data').append(_tweet_report_(obj.data))
                form.find('.report-data').append($('<div />', {
                    'class': 'card-panel',
                    'html': _tweet_report_(obj.data.original)
                }))
            }
            else
            {
                form.find('.report-data').append(_tweet_report_(obj.data))
            }
        break;
        case 'stats':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-stats',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            form.find('.report-stats').append($('<div />', { 'class': 'panel twitter' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel instagram' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel youtube-video' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel youtube-comment' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel news' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel blog' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel shopping' }))
            form.find('.report-stats').append($('<div />', { 'class': 'panel sozluk' }))

            form.append($('<input />', {
                'type': 'hidden',
                'name': 'data',
                'value': JSON.stringify(obj.stats)
            }))

            if (obj.stats.counts.twitter_tweet)
            {
                form.find('.report-stats').children('.twitter').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(obj.stats.counts.twitter_tweet) }),
                            $('<span />', { 'class': 'label', 'html': 'twitter' })
                        ]
                    })
                )

                if (obj.stats.twitter.reach)
                {
                    form.find('.report-stats').children('.twitter').append($('<span />', { 'html': number_format(obj.stats.twitter.reach) + ' etkileşim (rt, qt, rp)', 'class': 'd-table' }))
                }

                if (obj.stats.twitter.unique_users)
                {
                    form.find('.report-stats').children('.twitter').append($('<span />', { 'html': number_format(obj.stats.twitter.unique_users) + ' farklı kullanıcı', 'class': 'd-table' }))
                }

                if (obj.stats.twitter.verified_users)
                {
                    form.find('.report-stats').children('.twitter').append($('<span />', { 'html': number_format(obj.stats.twitter.verified_users) + ' tanınmış hesap', 'class': 'd-table' }))
                }

                if (obj.stats.twitter.mentions)
                {
                    form.find('.report-stats').children('.twitter').append($('<span />', { 'html': number_format(obj.stats.twitter.mentions) + ' mention', 'class': 'd-table' }))
                }

                if (obj.stats.twitter.hashtags)
                {
                    form.find('.report-stats').children('.twitter').append($('<span />', { 'html': number_format(obj.stats.twitter.hashtags) + ' hashtag', 'class': 'd-table' }))
                }

                if (obj.stats.twitter.followers)
                {
                    form.find('.report-stats').children('.twitter').append($('<span />', { 'html': number_format(parseInt(obj.stats.twitter.followers)) + ' ortalama takipçi', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.instagram_media)
            {
                form.find('.report-stats').children('.instagram').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.instagram_media)) }),
                            $('<span />', { 'class': 'label', 'html': 'instagram' })
                        ]
                    })
                )

                if (obj.stats.instagram.unique_users)
                {
                    form.find('.report-stats').children('.instagram').append($('<span />', { 'html': number_format(obj.stats.instagram.unique_users) + ' farklı kullanıcı', 'class': 'd-table' }))
                }

                if (obj.stats.instagram.mentions)
                {
                    form.find('.report-stats').children('.instagram').append($('<span />', { 'html': number_format(obj.stats.instagram.mentions) + ' mention', 'class': 'd-table' }))
                }

                if (obj.stats.instagram.hashtags)
                {
                    form.find('.report-stats').children('.instagram').append($('<span />', { 'html': number_format(obj.stats.instagram.hashtags) + ' hashtag', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.sozluk_entry)
            {
                form.find('.report-stats').children('.sozluk').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.sozluk_entry)) }),
                            $('<span />', { 'class': 'label', 'html': 'sözlük' })
                        ]
                    })
                )

                if (obj.stats.sozluk.unique_users)
                {
                    form.find('.report-stats').children('.sozluk').append($('<span />', { 'html': number_format(obj.stats.sozluk.unique_users) + ' farklı kullanıcı', 'class': 'd-table' }))
                }

                if (obj.stats.sozluk.unique_topics)
                {
                    form.find('.report-stats').children('.sozluk').append($('<span />', { 'html': number_format(obj.stats.sozluk.unique_topics) + ' farklı başlık', 'class': 'd-table' }))
                }

                if (obj.stats.sozluk.unique_sites)
                {
                    form.find('.report-stats').children('.sozluk').append($('<span />', { 'html': number_format(obj.stats.sozluk.unique_sites) + ' farklı sözlük sitesi', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.media_article)
            {
                form.find('.report-stats').children('.news').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.media_article)) }),
                            $('<span />', { 'class': 'label', 'html': 'haber' })
                        ]
                    })
                )

                if (obj.stats.news.unique_sites)
                {
                    form.find('.report-stats').children('.news').append($('<span />', { 'html': number_format(obj.stats.news.unique_sites) + ' farklı haber sitesi', 'class': 'd-table' }))
                }

                if (obj.stats.news.local_states)
                {
                    form.find('.report-stats').children('.news').append($('<span />', { 'html': number_format(obj.stats.news.local_states) + ' yerel medya', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.blog_document)
            {
                form.find('.report-stats').children('.blog').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.blog_document)) }),
                            $('<span />', { 'class': 'label', 'html': 'blog' })
                        ]
                    })
                )

                if (obj.stats.blog.unique_sites)
                {
                    form.find('.report-stats').children('.blog').append($('<span />', { 'html': number_format(obj.stats.blog.unique_sites) + ' farklı blog sitesi', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.youtube_video)
            {
                form.find('.report-stats').children('.youtube-video').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.youtube_video)) }),
                            $('<span />', { 'class': 'label', 'html': 'youtube video' })
                        ]
                    })
                )

                if (obj.stats.youtube_video.unique_users)
                {
                    form.find('.report-stats').children('.youtube-video').append($('<span />', { 'html': number_format(obj.stats.youtube_video.unique_users) + ' farklı kullanıcı', 'class': 'd-table' }))
                }

                if (obj.stats.youtube_video.hashtags)
                {
                    form.find('.report-stats').children('.youtube-video').append($('<span />', { 'html': number_format(obj.stats.youtube_video.hashtags) + ' hashtag', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.youtube_comment)
            {
                form.find('.report-stats').children('.youtube-comment').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.youtube_comment)) }),
                            $('<span />', { 'class': 'label', 'html': 'youtube yorum' })
                        ]
                    })
                )

                if (obj.stats.youtube_comment.unique_users)
                {
                    form.find('.report-stats').children('.youtube-comment').append($('<span />', { 'html': number_format(obj.stats.youtube_comment.unique_users) + ' farklı kullanıcı', 'class': 'd-table' }))
                }

                if (obj.stats.youtube_comment.unique_videos)
                {
                    form.find('.report-stats').children('.youtube-comment').append($('<span />', { 'html': number_format(obj.stats.youtube_comment.unique_videos) + ' video', 'class': 'd-table' }))
                }
            }

            if (obj.stats.counts.shopping_product)
            {
                form.find('.report-stats').children('.shopping').append(
                    $('<span />', {
                        'class': 'total',
                        'html': [
                            $('<span />', { 'class': 'count', 'html': number_format(parseInt(obj.stats.counts.shopping_product)) }),
                            $('<span />', { 'class': 'label', 'html': 'ürün' })
                        ]
                    })
                )

                if (obj.stats.shopping.unique_users)
                {
                    form.find('.report-stats').children('.shopping').append($('<span />', { 'html': number_format(obj.stats.shopping.unique_users) + ' farklı satıcı', 'class': 'd-table' }))
                }

                if (obj.stats.shopping.unique_sites)
                {
                    form.find('.report-stats').children('.shopping').append($('<span />', { 'html': number_format(obj.stats.shopping.unique_sites) + ' farklı site', 'class': 'd-table' }))
                }
            }
        break;
        case 'chart':
            var id = 'chart-' + Math.floor(Math.random() * 1000000);

            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-chart',
                        'css': { 'min-width': '70%' },
                        'html': $('<div />', {
                            'id': id,
                            'html': 'Yükleniyor...'
                        })
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            setTimeout(function() {
                $('#' + id).html('')

                try
                {
                    var reportChart = new ApexCharts(document.querySelector('#' + id), obj.data ? obj.data : $.parseJSON(obj));
                        reportChart.render()
                }
                catch (e)
                {
                    $('#' + id).html('Grafik Yüklenemedi!')
                }
            }, 1000)

            form.append($('<input />', {
                'type': 'hidden',
                'name': 'data',
                'value': obj.data ? JSON.stringify(obj.data) : obj
            }))
        break;
        case 'tr_map':
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill d-flex',
                        'css': { 'min-width': '70%' },
                        'html': [
                            $('<div />', {
                                'class': 'tr-map align-self-center'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            var data = obj.data ? obj.data : $.parseJSON(obj);

            var total = 0;

            $.each(data, function(key, o) {
                total = total + o.doc_count;
            })

            $.each(data, function(key, o) {
                var per = parseInt(o.doc_count*255)/total;
                var cr = per,
                    cg = 0,
                    cb = 0,
                    color = 'rgba(' + cr + ', ' + cg + ', ' + cb + ')';

                form.find('.tr-map').append($('<small />', {
                    'class': 'state state-' + getSlug(o.key),
                    'data-title': o.key,
                    'html': o.doc_count,
                    'css': { 'background-color': color }
                }))
            })

            form.append($('<input />', {
                'type': 'hidden',
                'name': 'data',
                'value': JSON.stringify(data)
            }))
        break;
        case 'twitterMentions':
        case 'twitterInfluencers':
        case 'twitterUsers':
        case 'youtubeUsers':
        case 'youtubeComments':
        case 'sozlukSites':
        case 'sozlukUsers':
        case 'sozlukTopics':
        case 'newsSites':
        case 'blogSites':
        case 'shoppingSites':
        case 'shoppingUsers':
            form.find('.report-page').addClass('table-graph')
            form.find('.content').html(
                [
                    $('<div />', {
                        'class': 'flex-fill report-table',
                        'css': { 'min-width': '70%' }
                    }),
                    $('<div />', {
                        'class': 'flex-fill textarea markdown',
                        'css': { 'min-width': '30%' },
                        'html': method == 'write' ? $('<textarea />', { 'name': 'text', 'placeholder': 'Metin Alanı', 'html': obj.page ? obj.page.text : '' }) : obj.page ? obj.page.text : ''
                    })
                ]
            )

            var title = '';
            var subtitle = '';
            var table = __report__table__generate();
            var data = obj.data ? obj.data : $.parseJSON(obj);

            switch (type)
            {
                case 'twitterMentions':
                    title = 'Twitter: Konuşulan Kişiler';
                    subtitle = 'Paylaşımlarda en fazla adı geçen başlıca hesaplar.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Adı' }),
                                $('<th />', { 'html': 'Kullanıcı Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Tweet Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, bucket) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.mention.name }),
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.mention.screen_name }),
                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                ]
                            })
                        )
                    })
                break;
                case 'twitterInfluencers':
                    title = 'Twitter: En Yüksek Takipçi';
                    subtitle = 'Konuya dahil olan kişiler. Takipçi sayılarına göre ilk 100 hesap.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Adı' }),
                                $('<th />', { 'html': 'Kullanıcı Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Tweet Sayısı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Takipçi Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, bucket) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.user.name }),
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.user.screen_name }),
                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count }),
                                    $('<td />', { 'class': 'right-align', 'html': number_format(bucket.properties.hits.hits[0]._source.user.counts.followers) })
                                ]
                            })
                        )
                    })
                break;
                case 'twitterUsers':
                    title = 'Twitter: En Çok Tweet';
                    subtitle = 'Konuyla ilgili en fazla tweet paylaşan hesaplar.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Adı' }),
                                $('<th />', { 'html': 'Kullanıcı Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Tweet Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, bucket) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.user.name }),
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.user.screen_name }),
                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                ]
                            })
                        )
                    })
                break;
                case 'youtubeUsers':
                    title = 'YouTube: En Çok Video';
                    subtitle = 'Konu hakkında en fazla video yükleyen başlıca kullanıcılar.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Kanal Kimliği' }),
                                $('<th />', { 'html': 'Kanal Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Video Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, bucket) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': bucket.key }),
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.channel.title }),
                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                ]
                            })
                        )
                    })
                break;
                case 'youtubeComments':
                    title = 'YouTube: En Çok Yorum';
                    subtitle = 'Konu hakkında en fazla yorum yapan ilk 100 kullanıcı.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Kanal Kimliği' }),
                                $('<th />', { 'html': 'Kanal Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Yorum Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(bucket_key, bucket) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': bucket.key }),
                                    $('<td />', { 'html': bucket.properties.hits.hits[0]._source.channel.title }),
                                    $('<td />', { 'class': 'right-align', 'html': bucket.doc_count })
                                ]
                            })
                        )
                    })
                break;
                case 'sozlukSites':
                    title = 'Sözlükler';
                    subtitle = 'Konu hakkında entry girilen sözlükler.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Sözlük Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Entry Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.name }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
                case 'sozlukUsers':
                    title = 'Sözlük Yazarları';
                    subtitle = 'Konu hakkında en çok entry giren sözlük yazarları.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Sözlük Adı' }),
                                $('<th />', { 'html': 'Yazar Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Entry Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.site }),
                                    $('<td />', { 'html': o.name }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
                case 'sozlukTopics':
                    title = 'Sözlük Başlıkları';
                    subtitle = 'Konu hakkında en çok entry girilen başlıklar.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Sözlük Adı' }),
                                $('<th />', { 'html': 'Başlık' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Entry Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.site }),
                                    $('<td />', { 'html': o.title }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
                case 'newsSites':
                    title = 'Haber Siteleri';
                    subtitle = 'Konu hakkında en çok haber yapan haber siteleri.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Site Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'Haber Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.name }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
                case 'blogSites':
                    title = 'Bloglar';
                    subtitle = 'Konu hakkında en çok blog yazısı paylaşan bloglar.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Site Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'İçerik Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.name }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
                case 'shoppingSites':
                    title = 'E-ticaret Siteleri';
                    subtitle = 'Konu hakkında en çok ilan yayınlanan e-ticaret siteleri.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Site Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'İlan Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.name }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
                case 'shoppingUsers':
                    title = 'E-ticaret Satıcıları';
                    subtitle = 'Konu hakkında en çok ürün yayınlayan e-ticaret satıcıları.';

                    table.children('thead').append(
                        $('<tr />', {
                            'html': [
                                $('<th />', { 'html': 'Site Adı' }),
                                $('<th />', { 'html': 'Satıcı Adı' }),
                                $('<th />', { 'class': 'right-align', 'html': 'İlan Sayısı' })
                            ]
                        })
                    )

                    $.each(data, function(key, o) {
                        table.children('tbody').append(
                            $('<tr />', {
                                'html': [
                                    $('<td />', { 'html': o.site }),
                                    $('<td />', { 'html': o.name }),
                                    $('<td />', { 'class': 'right-align', 'html': o.hit })
                                ]
                            })
                        )
                    })
                break;
            }

            try
            {
                form.find('input[name=title]').val(obj.page.title ? obj.page.title : title)
                form.find('input[name=subtitle]').val(obj.page.subtitle ? obj.page.subtitle : subtitle)
            }
            catch (e)
            {
                form.find('input[name=title]').val(title)
                form.find('input[name=subtitle]').val(subtitle)
            }

            form.find('.report-table').html(table)

            form.append($('<input />', {
                'type': 'hidden',
                'name': 'data',
                'value': JSON.stringify(data)
            }))
        break;
    }

    form.find('.report-page').addClass(type)
}

function __report__page_form(options)
{
    return $('<form />', {
        'method': options.method,
        'action': options.action,
        'id': 'report-page',
        'class': 'json align-self-center',
        'data-callback': options.callback,
        'data-type': options.type,
        'html': [
            $('<div />', {
                'class': 'show-on-1024-and-up',
                'html': [
                    $('<div />', {
                        'class': 'report-page',
                        'html': [
                            $('<input />', {
                                'type': 'text',
                                'name': 'title',
                                'placeholder': 'Başlık',
                                'maxlength': 64
                            }),
                            $('<input />', {
                                'type': 'text',
                                'name': 'subtitle',
                                'placeholder': 'Alt Başlık',
                                'maxlength': 128
                            }),
                            $('<div />', {
                                'class': 'logo',
                                'html': [
                                    $('<img />', {
                                        'alt': 'Logo',
                                        'src': '/img/olive_logo-grey.svg'
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'content d-flex align-items-stretch'
                            }).html(options.content ? options.content : ''),
                            $('<span />', {
                                'class': 'date',
                                'html': [
                                    $('<small />', options.date_1 ? options.date_1 : ''),
                                    $('<small />', options.date_2 ? options.date_2 : '')
                                ]
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'report-tools',
                        'html': [
                            $('<a />', {
                                'href': '#',
                                'data-trigger': 'report-page_save',
                                'data-type': options.type,
                                'class': 'btn-floating btn-flat white waves-effect',
                                'html': $('<i />', {
                                    'class': 'material-icons',
                                    'html': 'save'
                                })
                            })
                        ]
                    })
                ]
            }),
            $('<div />', {
                'class': 'olive-alert warning hide-on-1024-and-up',
                'html': [
                    $('<div />', { 'class': 'anim' }),
                    $('<h4 />', { 'class': 'mb-1', 'html': 'Yetersiz Pencere' }),
                    $('<p />', { 'class': 'mb-1', 'html': 'Pencere boyutunuz bu sayfayı kullanmak için yeterli seviyede değil.' }),
                    $('<p />', { 'class': 'mb-1', 'html': 'Lütfen en az 1024px genişliğinde bir pencere ile tekrar deneyin.' })
                ]
            })
        ]
    });
}

function __report__page_create(__, obj)
{
    if (obj.status == 'ok')
    {
        var menu = $('#report-menu');

        $('body').removeClass('fpw-active')

        menu.addClass('show')

        setTimeout(function() {
            menu.removeClass('show')
        }, 2000)

        flash_alert('Rapora Eklendi!', 'green white-text')
    }
}

function __report__elements()
{
    $('.report-page > .content > .lines > .draggable').draggable({
        'handle': '.drag-handle',
        'containment': '.lines',
        'snap': true
    })
}

$(document).on('click', '#report-menu.active [data-report-element=add-page]', function() {
    var form = __report__page_form(
        {
            'action': '/raporlar/sayfa',
            'method': 'put',
            'callback': '__report__page_create',
            'content': [
                $('<div />', {
                    'class': 'flex-fill lines',
                    'css': { 'width': '50%' }
                }),
                $('<div />', {
                    'class': 'flex-fill textarea markdown',
                    'css': { 'width': '50%' },
                    'html': $('<textarea />', {
                        'name': 'text',
                        'placeholder': 'Metin Alanı'
                    })
                })
            ],
            'type': 'lines'
        }
    );

        form.find('.report-tools').prepend(
            $('<a />', {
                'href': '#',
                'class': 'btn-floating btn-flat white waves-effect',
                'data-report-element': 'line',
                'html': $('<i />', {
                    'class': 'material-icons',
                    'html': 'text_format'
                })
            }),
        );

    full_page_wrapper(form)

    form.find('input[name=title]').focus()
}).on('click', '[data-report-element]', function() {
    var __ = $(this);
    var event = false;

    var id = 'item-' + Math.floor(Math.random() * 1000000);

    switch (__.data('report-element'))
    {
        case 'line':
            var element = $('<div />', {
                'data-report-item': 'line',
                'data-max-item': 10,
                'class': 'draggable',
                'id': id,
                'html': [
                    $('<i />', {
                        'class': 'material-icons drag-handle',
                        'html': 'drag_handle'
                    }),
                    $('<i />', {
                        'class': 'material-icons delete',
                        'html': 'delete',
                        'data-remove': '#' + id
                    }),
                    $('<input />', {
                        'placeholder': 'Satır',
                        'type': 'text',
                        'class': 'auto-width',
                        'maxlength': '50'
                    })
                ]
            });

            event = true;
        break;
    }

    if (event)
    {
        if (element.data('max-item') && $('[data-report-item=' + element.data('report-item') + ']').length >= element.data('max-item'))
        {
            M.toast({ 'html': 'Bu elemandan daha fazla kullanamazsınız.', 'classes': 'red' })
        }
        else
        {
            switch (__.data('report-element'))
            {
                case 'line':
                    $('.report-page').find('.lines').append(element.effect('highlight', { 'color': '#ccc' }, 1000))

                    element.find('input[type=text]').focus()
                break;
            }
        }

        __report__elements()
    }
}).on('click', '[data-help=report]', function() {
    return modal({
        'id': 'info',
        'body': [
            $('<p />', { 'html': '"Olive Rapor Aracı" sayesinde, araştırmanızı yaparken eş zamanlı olarak raporunuzu da oluşturabilirsiniz.' }),
            $('<ol />', {
                'html': [
                    $('<li />', { 'html': 'Olive ekranının alt kısmında bulunan menüden "Rapor Başlat" butonuna tıklayın ve rapor adı ile birlikte yeni bir rapor başlatın.' }),
                    $('<li />', { 'html': 'Rapor başlatıldıktan sonra, diğer Olive araçlarını kullanarak araştırmanıza başlayın.' }),
                    $('<li />', { 'html': 'Araştırmanız esnasında elde edeceğiniz önemli verilerin, grafiklerin, tabloların veya göstergelerin etrafında bulunan "Rapora Ekle" butonuna tıklayın.' }),
                    $('<li />', { 'html': 'Açılan pencereden, varsa; başlık, alt başlık veya açıklama gibi seçeneklerinizi girin.' }),
                    $('<li />', { 'html': 'Hazır olan sayfayı kayıt butonuna basarak raporunuza ekleyin.' }),
                    $('<li />', { 'html': 'Harici bir sayfa eklemek için, rapor menüsünden "Yeni Sayfa" butonuna tıklayarak benzer işlemi tekrar edebilirsiniz.' }),
                    $('<li />', { 'html': 'Harici sayfa eklerken; sadece başlık veya alt başlık girerek, başlık sayfası oluşturabilirsiniz.' }),
                    $('<li />', { 'html': 'Ayrıca yine harici bir sayfa eklerken; satır özelliğini kullanarak rapor sayfasının %50\'lik kısmında sürüklenebilir satırlar oluşturabilirsiniz.' }),
                    $('<li />', { 'html': 'Rapor menüsünden istediğiniz zaman raporunuzun son halini görmek için Önizleme yapabilirsiniz.' }),
                    $('<li />', { 'html': 'Raporunuzu tamamlarken veya tamamladıktan sonra, özel bir şifre tanımlayabilirsiniz. Böylece sadece şifreye sahip kişiler raporunuza erişebilir.' }),
                    $('<li />', { 'html': 'Rapor tamamlandıktan sonra yeni bir sayfa eklenemez. Ancak mevcut sayfalar düzenlenebilir, sayfa sıralamaları değiştirilebilir veya mevcut sayfalar silinebilir.' }),
                    $('<li />', { 'html': 'Rapor adını, verilerin alındığı tarihi ifade eden tarih aralığını ve rapor şifresini de istediğiniz zaman güncelleyebilirsiniz.' }),
                    $('<li />', { 'html': 'Daha önceden oluşturulmuş, şifreye sahip raporların mevcut şifrelerini görebilmek için, "Raporlar" sayfasında ilgili rapor satırında bulunan "Yıldız" simgesini kullanabilirsiniz.' }),
                ]
            })
        ],
        'title': keywords.help,
        'options': {
            dismissible: false
        },
        'size': 'modal-large',
        'options': {},
        'footer': [
           $('<a />', {
               'href': '#',
               'class': 'modal-close waves-effect btn-flat',
               'html': keywords.ok
           })
        ]
    })
}).on('click', '[data-trigger=report-page_save]', function() {
    var __ = $(this);
    var form = $('form#report-page');

    if (__.data('type') == 'lines' || __.data('type') == 'title')
    {
        var line_ground = $('.report-page > .content > .lines');
        var data = [];

        $.each(line_ground.children('[data-report-item=line]'), function() {
            var __ = $(this);
            var item = { 'text': __.find('input[type=text]').val() };
                item.position = __.position()

            data.push(item)
        })

        var lines_input = form.find('input[name=lines]');

        if (data.length)
        {
            var input = $('<input />', {
                'name': 'lines',
                'type': 'hidden',
                'value': JSON.stringify(data)
            })

            if (!lines_input.length)
            {
                form.append(input)
            }
        }
        else
        {
            lines_input.remove()
        }
    }

    vzAjax(form)
})

function __report__status(__, obj)
{
    if (obj.status == 'ok')
    {
        if (obj.data.status === false && obj.data.validate === false)
        {
            var mdl = modal({
                'id': 'report',
                'body': $('<form />', {
                    'method': 'post',
                    'action': '/raporlar/rapor-baslat',
                    'id': 'report-new',
                    'class': 'json',
                    'data-callback': '__report__start',
                    'html': [
                        $('<div />', {
                            'class': 'input-field',
                            'html': [
                                $('<input />', {
                                    'id': 'name',
                                    'name': 'name',
                                    'type': 'text',
                                    'class': 'validate'
                                }),
                                $('<label />', {
                                    'for': 'name',
                                    'html': 'Rapor Adı/Başlığı'
                                }),
                                $('<span />', {
                                    'class': 'helper-text',
                                    'html': 'Oluşturacağınız rapor için başlık veya isim belirtin.'
                                })
                            ]
                        }),
                        $('<ol />', {
                            'class': 'blue-grey-text text-darken-2',
                            'html': [
                                $('<li />', { 'html': 'Raporu başlattıktan sonra; araştırmanız esnasında karşılaşacağınız içerik ve grafiklerin altında bulunan rapor simgesine tıklayarak veya rapor bölmesini kullanarak raporunuzu geliştirin.' }),
                                $('<li />', { 'html': 'Raporunuzun hazır olduğunda rapor bölmesinden "Raporu Tamamla" butonuna basın.' }),
                                $('<li />', { 'html': 'Oluşturduğunuz raporun çıktısını almak için rapor bölmesinden "Raporlar" butonuna basın.' }),
                            ]
                        })
                    ]
                }),
                'size': 'modal-medium',
                'title': 'Rapor Başlat',
                'options': {
                    dismissible: false
                },
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect btn-flat grey-text',
                        'html': keywords.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<button />', {
                        'type': 'submit',
                        'class': 'waves-effect btn-flat',
                        'data-submit': 'form#report-new',
                        'html': keywords.ok
                    })
                ]
            })

            mdl.find('input[name=name]').focus()

            return mdl;
        }
        else if (obj.data.status === true && obj.data.validate === false)
        {
            return modal({
                'id': 'alert',
                'body': [
                    $('<div />', {
                        'class': 'input-field',
                        'html': [
                            $('<input />', {
                                'id': 'report_password',
                                'name': 'report_password',
                                'type': 'text',
                                'class': 'validate',
                                'placeholder': 'Şifre'
                            }),
                            $('<label />', {
                                'for': 'report_password',
                                'html': 'Rapor Şifresi'
                            }),
                            $('<span />', {
                                'class': 'helper-text',
                                'html': 'Raporu açacak kişiler için rapor şifresi. Boş bırakılırsa, rapor herkes tarafından açılabilir. (İsteğe Bağlı)'
                            })
                        ]
                    }),
                    $('<div />', {
                        'class': 'd-flex justify-content-between',
                        'html': [
                            $('<div />', {
                                'class': 'input-field flex-fill',
                                'html': [
                                    $('<input />', {
                                        'id': 'report_date_1',
                                        'name': 'report_date_1',
                                        'type': 'date',
                                        'class': 'validate',
                                        'placeholder': 'Tarih 1'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text',
                                        'html': '1. Tarih (İsteğe Bağlı)'
                                    })
                                ]
                            }),
                            $('<div />', {
                                'class': 'input-field flex-fill',
                                'html': [
                                    $('<input />', {
                                        'id': 'report_date_2',
                                        'name': 'report_date_2',
                                        'type': 'date',
                                        'class': 'validate',
                                        'placeholder': 'Tarih 2'
                                    }),
                                    $('<span />', {
                                        'class': 'helper-text',
                                        'html': '2. Tarih (İsteğe Bağlı)'
                                    })
                                ]
                            })
                        ]
                    }),
                    $('<p />', { 'html': 'Raporu tamamladıktan sonra "Raporlar" bölümünden tekrar değişiklik yapabilirsiniz.' })
                ],
                'size': 'modal-medium',
                'title': 'Raporu Tamamla',
                'footer': [
                    $('<a />', {
                        'href': '#',
                        'class': 'modal-close waves-effect grey-text btn-flat',
                        'html': keywords.cancel
                    }),
                    $('<span />', {
                        'html': ' '
                    }),
                    $('<a />', {
                        'href': '#',
                        'class': 'waves-effect btn-flat json',
                        'html': keywords.ok,
                        'data-href': '/raporlar/durum?validate=on',
                        'data-method': 'post',
                        'data-include': 'report_date_1,report_date_2,report_password',
                        'data-callback': '__report__status'
                    })
                ],
                'options': {}
            })
        }
        else
        {
            flash_alert('Rapor Tamamlandı!', 'orange white-text')

            $('#modal-alert').modal('close')

            $('#report-menu').addClass('show')
                             .removeClass('active')
                             .attr('date-1', '')
                             .attr('date-2', '')

            var start_trigger = $('[data-name=report-trigger]');
                start_trigger.removeClass('red-text')
                start_trigger.children('span').html('Rapor Başlat')
                start_trigger.children('i.material-icons').html('fiber_manual_record')

            setTimeout(function() {
                $('#report-menu').removeClass('show')
            }, 2000)
        }
    }
}

function __report__start(__, obj)
{
    if (obj.status == 'ok')
    {
        flash_alert('Rapor Başlatıldı!', 'green white-text')

        $('#modal-report').modal('close')

        $('#report-menu').addClass('show active').attr('data-source', obj.data)

        setTimeout(function() {
            $('#report-menu').removeClass('show')
        }, 2000)

        $('#report-menu').addClass('active')

        var start_trigger = $('[data-name=report-trigger]');
            start_trigger.addClass('red-text')
            start_trigger.children('span').html('Raporu Tamamla')
            start_trigger.children('i.material-icons').html('stop')
    }
}

function __report__data(__, obj)
{
    if (obj.status == 'ok')
    {
        var form = __report__page_form(
            {
                'action': '/raporlar/icerik',
                'method': 'put',
                'callback': '__report__page_create',
                'type': __.data('type')
            }
        );

        form.data('id', __.data('id'))
        form.data('type', __.data('type'))
        form.data('index', __.data('index'))

        switch (__.data('type'))
        {
            case 'article':
                form.find('input[name=title]').val('Haber')
                form.find('input[name=subtitle]').val(obj.data.url)
            break;
            case 'entry':
                form.find('input[name=title]').val('Sözlük')
                form.find('input[name=subtitle]').val(obj.data.url)
            break;
            case 'media':
                form.find('input[name=title]').val('Instagram, Medya')
                form.find('input[name=subtitle]').val('https://www.instagram.com/p/' + obj.data.shortcode + '/')
            break;
            case 'document':
                form.find('input[name=title]').val('Blog & Forum')
                form.find('input[name=subtitle]').val(obj.data.url)
            break;
            case 'comment':
                form.find('input[name=title]').val('YouTube, Yorum')
                form.find('input[name=subtitle]').val('https://www.youtube.com/watch?v=' + obj.data.video_id)
            break;
            case 'video':
                form.find('input[name=title]').val('YouTube, Video')
                form.find('input[name=subtitle]').val('https://www.youtube.com/watch?v=' + obj.data.id)
            break;
            case 'product':
                form.find('input[name=title]').val('E-ticaret')
                form.find('input[name=subtitle]').val(obj.data.url)
            break;
            case 'tweet':
                form.find('input[name=title]').val('Twitter, Tweet')
                form.find('input[name=subtitle]').val('https://twitter.com/' + obj.data.user.screen_name + '/status/' + obj.data.id)
            break;
        }

        __report__pattern(obj, form, __.data('type'), 'write')

        full_page_wrapper(form)

        form.find('input[name=title]').focus()
    }
}

function __report__aggs(__, obj)
{
    if (obj.status == 'ok')
    {
        var form = __report__page_form(
            {
                'action': '/raporlar/aggs',
                'method': 'put',
                'callback': '__report__page_create',
                'type': __.data('report')
            }
        );

        switch (__.data('report'))
        {
            case 'stats':
                form.find('input[name=title]').val('Sayılar')
                form.find('input[name=subtitle]').val('İlgili konu toplamda ' + number_format(obj.stats.hits) + ' web paylaşımında yer aldı.')
            break;
        }

        __report__pattern(obj, form, __.data('report'), 'write')

        full_page_wrapper(form)

        form.find('input[name=title]').focus()
    }
}

function __report__table__generate()
{
    var table = $('<table />', {
        'class': 'highlight',
        'html': [
            $('<thead />'),
            $('<tbody />')
        ]
    })

    return table;
}

function __report__chart_clear(data)
{
    var ndata = $.parseJSON(JSON.stringify(data));
        ndata.chart.toolbar.show = false;
        ndata.grid = {
            borderColor: 'transparent',
            row: {
                colors: [ 'transparent', 'transparent' ]
            }
        };
        ndata.title.text = 'Grafik';
        ndata.chart.height = 400;

        delete ndata.xaxis.title;
        delete ndata.yaxis;

        if (ndata.subtitle)
        {
            ndata.title.text = ndata.subtitle.text;

            delete ndata.subtitle;
        }

    return JSON.stringify(ndata);
}
