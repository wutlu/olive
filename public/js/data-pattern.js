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
        'class': 'card card-unstyled',
        'html': [
            $('<time>', {
                'html': o.created_at,
                'class': 'd-table mb-1'
            }),

            $('<div />', {
                'class': 'card-sentiment d-flex justify-content-between mb-1',
                'html': [
                    $('<div />', {
                        'css': {
                            'width': (o.sentiment.pos*100) + '%'
                        },
                        'class': 'sentiment-item light-green-text accent-4 d-flex flex-fill',
                        'html': [
                            $('<i />', {
                                'class': 'material-icons light-green-text align-self-center',
                                'html': 'sentiment_very_satisfied'
                            }),
                            $('<span />', {
                                'class': 'badge light-green-text align-self-center',
                                'html': (o.sentiment.pos*100)
                            }).addClass(o.sentiment.pos < 0.2 ? 'hide' : '')
                        ]
                    }),

                    $('<div />', {
                        'css': {
                            'width': (o.sentiment.neu*100) + '%'
                        },
                        'class': 'sentiment-item grey-text d-flex flex-fill',
                        'html': [
                            $('<i />', {
                                'class': 'material-icons grey-text align-self-center',
                                'html': 'sentiment_neutral'
                            }),
                            $('<span />', {
                                'class': 'badge grey-textelf-center',
                                'html': (o.sentiment.neu*100)
                            }).addClass(o.sentiment.neu < 0.2 ? 'hide' : '')
                        ]
                    }),

                    $('<div />', {
                        'css': {
                            'width': (o.sentiment.neg*100) + '%'
                        },
                        'class': 'sentiment-item red-text accent-4 d-flex flex-fill',
                        'html': [
                            $('<i />', {
                                'class': 'material-icons red-text align-self-center',
                                'html': 'sentiment_very_dissatisfied'
                            }),
                            $('<span />', {
                                'class': 'badge red-text align-self-center',
                                'html': (o.sentiment.neg*100)
                            }).addClass(o.sentiment.neg < 0.2 ? 'hide' : '')
                        ]
                    })
                ]
            }),

            $('<a />', {
                'class': 'btn-floating btn-small waves-effect white',
                'href': '/db/' + o._index + '/' + o._type + '/' + o._id,
                'html': $('<i />', {
                    'class': 'material-icons grey-text text-darken-2',
                    'html': 'info'
                })
            }),
            $('<span />', { 'html': ' ' }),
            $('<a />', {
                'href': '#',
                'html': $('<i />', {
                    'class': 'material-icons grey-text text-darken-2',
                    'html': 'add'
                }),
                'class': 'btn-floating btn-small waves-effect white json',
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
    return $('<div />', {
        'class': 'data',
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
                        'class': 'mr-1 align-self-center'
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
}

function _entry_(o)
{
    return $('<div />', {
        'class': 'data',
        'html': [
            $('<span />', {
                'html': o.title,
                'class': 'd-table blue-text title text-area'
            }),
            $('<span />', {
                'html': o.author,
                'class': 'd-table red-text'
            }),
            $('<span />', {
                'html': o.text,
                'class': 'grey-text text-darken-2 text-area'
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
}

function _article_(o)
{
    var article = $('<div />', {
        'class': 'data',
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
                                'class': 'd-table blue-text title text-area'
                            }),
                            $('<span />', {
                                'html': o.text,
                                'class': 'grey-text text-darken-2 text-area'
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

function _product_(o)
{
    return $('<div />', {
        'class': 'data',
        'html': [
            $('<span />', {
                'html': o.title,
                'class': 'd-table blue-text title text-area'
            }),
            $('<span />', {
                'html': o.text ? o.text : 'Açıklama Yok',
                'class': 'grey-text text-darken-2 text-area'
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
}

function _comment_(o)
{
    return $('<div />', {
        'class': 'data',
        'html': [
            $('<a />', {
                'html': o.channel.title,
                'href': 'https://www.youtube.com/channel/' + o.channel.id,
                'class': 'd-table red-text text-area'
            }).attr('target', '_blank'),
            $('<span />', {
                'html': o.text,
                'class': 'grey-text text-darken-2 text-area'
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
        'class': 'data',
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
                                'class': 'd-table blue-text title text-area'
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