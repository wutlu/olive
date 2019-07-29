/*!
 |-------------------------------
 | veri.zone 1.0
 |-------------------------------
 | (c) 2019 - veri.zone
 |-------------------------------
 */

function __joints(o)
{
    var card = $('<div />', {
        'html': [
            $('<time>', {
                'html': o.created_at,
                'class': 'd-table mb-1'
            }),
            $('<a />', {
                'class': 'btn-flat waves-effect',
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
                'class': 'btn-flat waves-effect json',
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
                'class': 'd-flex mb-1',
                'html': [
                    $('<img />', {
                        'src': o.user.image,
                        'alt': 'Avatar',
                        'onerror': "this.onerror=null;this.src='/img/no_image-twitter.svg';",
                        'css': {
                            'width': '48px',
                            'height': '48px'
                        },
                        'class': 'mr-1 align-self-center tweet-avatar'
                    }),
                    $('<div />', {
                        'class': 'align-self-center',
                        'html': [
                            $('<a />', {
                                'html': o.user.name,
                                'href': 'https://twitter.com/' + o.user.screen_name,
                                'class': 'd-table red-text'
                            }).attr('target', 'blank'),
                            $('<span />', {
                                'html': '@' + o.user.screen_name,
                                'class': 'd-table grey-text'
                            })
                        ]
                    }),
                    $('<i />', {
                        'class': 'material-icons cyan-text hide ml-1',
                        'html': 'check'
                    }).removeClass(o.user.verified ? 'hide' : '')
                ]
            }),
            $('<div />', {
                'class': 'media-area d-flex flex-wrap mb-1'
            }),
            $('<span />', {
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

                tweet.find('.media-area').append(img)
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
                }))
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
        media.append($('<span />', {
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
                'class': 'd-table title text-area'
            }),
            $('<span />', {
                'html': o.author,
                'class': 'd-table red-text'
            }),
            $('<span />', {
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
                                'class': 'd-table title text-area'
                            }),
                            $('<span />', {
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
                                'class': 'd-table title text-area'
                            }),
                            $('<span />', {
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
                'class': 'd-table title text-area'
            }),
            $('<span />', {
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
                'class': 'd-table red-text text-area'
            }).attr('target', '_blank'),
            $('<span />', {
                'html': o.text,
                'class': 'text-area'
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
                            $('<span />', {
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
