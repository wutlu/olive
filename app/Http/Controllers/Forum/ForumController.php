<?php

namespace App\Http\Controllers\Forum;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Forum\Category;
use App\Models\Forum\Message;
use App\Models\Forum\Follow;

use App\Http\Requests\IdRequest;
use App\Http\Requests\Forum\Kategori\UpdateRequest;
use App\Http\Requests\Forum\Kategori\CreateRequest;

use App\Http\Requests\Forum\MoveRequest;
use App\Http\Requests\Forum\VoteRequest;
use App\Http\Requests\Forum\ThreadRequest;
use App\Http\Requests\Forum\ReplyCreateRequest;
use App\Http\Requests\Forum\ReplyUpdateRequest;

use App\Utilities\UserActivityUtility;

use Validator;
use Cookie;
use Term;
use Carbon\Carbon;

use App\Models\User\User;

use App\Notifications\MessageNotification;
use App\Notifications\ForumNotification;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'threadStatus',
            'threadStatic',
            'messageDelete',
            'threadFollow',
            'messageBestAnswer',
            'messageSpam',
            'threadForm',
            'threadSave',
            'replyGet',
            'replyUpdate',
            'replySave',
            'threadMove',
        ]);

        $this->middleware('throttle:10,1')->only([
            'messageVote',
            'messageSpam',
        ]);

        $this->middleware('throttle:10,10')->only([
            'threadSave',
            'replySave',
        ]);

        $this->middleware('verification.email')->only([
            'replySave',
            'replyGet',
            'replyUpdate',
            'threadSave',
            'threadFollow',
            'messageBestAnswer',
        ]);
    }

    /**
     * forum ana sayfa
     */
    public static function index(int $pager = 10)
    {
        $data = Message::whereNull('message_id')->orderBy('updated_at', 'DESC')->simplePaginate($pager);

        return view('forum.index', compact('data'));
    }

    /**
     * forum kategori
     */
    public static function category(string $slug, int $pager = 10)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $data = $category->threads()->orderBy('static', 'DESC')->orderBy('updated_at', 'DESC')->simplePaginate($pager);

        return view('forum.index', compact('category', 'data'));
    }

    /**
     * forum grup
     */
    public static function group(string $group, string $section, int $pager = 10)
    {
        $data = new Message;
        $data = $data->whereNull('forum_messages.message_id');

        switch ($group)
        {
            case __('route.forum.thread'):
                if (auth()->guest()) return redirect()->route('user.login');

                switch ($section)
                {
                    case __('route.forum.my_threads'):
                        $title = 'Açılan Konular';

                        $data = $data->where('user_id', auth()->user()->id);
                    break;
                    case __('route.forum.included_threads'):
                        $title = 'Dahil Olunan Konular';

                        $data = $data->whereHas('replies', function($query) {
                            $query->where('user_id', auth()->user()->id);
                        });
                    break;
                    case __('route.forum.followed_threads'):
                        $title = 'Takip Edilen Konular';

                        $data = $data->select('forum_messages.*');
                        $data = $data->leftJoin('forum_follows', 'forum_follows.message_id', '=', 'forum_messages.id');
                        $data = $data->where('forum_follows.user_id', auth()->user()->id);
                    break;
                }
            break;
            case __('route.forum.question'):
                switch ($section)
                {
                    case __('route.forum.unanswered'):
                        $title = 'Yanıtlanmayan Sorular';

                        $data = $data->whereNotNull('question');
                        $data = $data->has('replies', '=', 0);
                    break;
                    case __('route.forum.solved'):
                        $title = 'Çözülen Sorular';

                        $data = $data->where('question', 'solved');
                    break;
                    case __('route.forum.unsolved'):
                        $title = 'Çözülmeyen Sorular';

                        $data = $data->where('question', 'unsolved');
                    break;
                }
            break;
            case __('route.forum.popular'):
                switch ($section)
                {
                    case __('route.forum.spam'):
                        $title = 'Spam Sıralaması';

                        if (!auth()->user()->moderator && !auth()->user()->root)
                        {
                            return abort(503);
                        }

                        $data = $data->where(function ($query) {
                            $query->orWhere(function ($query) {
                                $query->whereHas('replies', function($query) {
                                    $query->where('spam', '>', 0);
                                });
                            });
                            $query->orWhere('spam', '>', 0);
                        });
                        $data = $data->orderBy('forum_messages.spam', 'DESC');
                    break;
                    case __('route.forum.week'):
                        $title = 'Haftanın Popülerleri';

                        $date = Carbon::now()->subDays(7)->format('Y-m-d H:i:s');
                        $data->where('created_at', '>=', $date);
                        $data->orderBy('hit', 'DESC');
                    break;
                    case __('route.forum.all_time'):
                        $title = 'Tüm Zamanların Popülerleri';

                        $data->orderBy('hit', 'DESC');
                    break;
                }
            break;
            case __('route.forum.user'):
                $user = User::where('id', intval($section))->firstOrFail();

                $title = $user->name.', konuları';

                $data = $data->where('user_id', $user->id);
            break;
        }

        $data = $data->orderBy('forum_messages.updated_at', 'DESC');
        $data = $data->simplePaginate($pager);

        if (!@$title)
        {
            return abort(404);
        }

        return view('forum.index', compact('data', 'title'));
    }

    /**
     ****************************************************
     * SYSTEM FUNCTION
     ****************************************************
     *
     * takip edilen konulara verilen
     * cevaplar için e-posta bildirimleri.
     *
     */
    public static function threadFollowNotifications()
    {
        $threads = Message::whereNull('message_id')->where('notify', false)->get();

        if (count($threads))
        {
            foreach ($threads as $thread)
            {
                echo Term::line($thread->subject);

                foreach ($thread->followers as $follower)
                {
                    echo $follower->user->name.PHP_EOL;

                    if ($follower->user->notification('forum'))
                    {
                        $follower->user->notify(
                            (
                                new ForumNotification(
                                    $thread->subject,
                                    $thread->subject,
                                    'Takip ettiğiniz konuya cevap(lar) girildi.',
                                    $thread->route()
                                )
                            )->onQueue('email')
                        );
                    }
                }

                $thread->notify = true;
                $thread->save();
            }
        }
    }

    /**
     * forum cevap ekleme
     */
    public static function replySave(ReplyCreateRequest $request)
    {
        $user = auth()->user();

        $reply = Message::where('id', $request->reply_id)->firstOrFail();

        $thread = $reply->thread ? $reply->thread : $reply;
        $thread->updated_at = date('Y-m-d H:i:s');
        $thread->notify = false;
        $thread->save();

        $new = new Message;
        $new->body = $request->body;

        if ($request->reply_id != $thread->id)
        {
            $new->reply_id = $request->reply_id;
        }

        $new->message_id = $thread->id;
        $new->user_id = $user->id;
        $new->save();

        if ($thread->question)
        {
            if (!$user->badge(3))
            {
                if ($user->messages()->whereHas('thread', function($query) { $query->whereNotNull('question'); })->count() >= 10)
                {
                    $user->addBadge(3); // 10 cevap
                }
            }
            if (!$user->badge(5))
            {
                if ($user->messages()->whereHas('thread', function($query) { $query->whereNotNull('question'); })->count() >= 10)
                {
                    $user->addBadge(5); // 100 cevap
                }
            }
        }

        Follow::firstOrCreate([ 'user_id' => $user->id, 'message_id' => $thread->id ]);

        $paginate = Message::where(function($query) use ($thread) {
            $query->orWhere('id', $thread->id);
            $query->orWhere('message_id', $thread->id);
        })->orderBy('id', 'ASC')->paginate(10);

        return [
            'status' => 'ok',
            'data' => [
                'id' => $new->id,
                'last_page' => $paginate->lastPage()
            ]
        ];
    }

    /**
     * forum cevap json
     */
    public static function replyGet(int $id)
    {
        $reply = Message::select('id', 'body', 'user_id')->where('id', $id)->whereNotNull('message_id')->firstOrFail();

        if (!$reply->authority())
        {
            return abort(403);
        }

        return [
            'status' => 'ok',
            'data' => $reply
        ];
    }

    /**
     * forum cevap güncelle
     */
    public static function replyUpdate(ReplyUpdateRequest $request)
    {
        $reply = Message::select('id', 'body', 'user_id')->where('id', $request->id)->whereNotNull('message_id')->firstOrFail();

        if (!$reply->authority())
        {
            return abort(403);
        }

        $reply->body = $request->body;
        $reply->updated_user_id = auth()->user()->id;
        $reply->save();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $reply->id,
                'body' => $reply->markdown()
            ]
        ];
    }

    /**
     * forum yeni konu
     */
    public static function threadForm(int $id = 0)
    {
        $categories = Category::orderBy('sort')->get();

        if ($id)
        {
            $thread = Message::where('id', $id)->whereNull('message_id')->firstOrFail();

            if (!$thread->authority())
            {
                return abort(403);
            }
        }
        else
        {
            $thread = [];
        }

        return view('forum.thread_form', compact('categories', 'thread'));
    }

    /**
     * forum konu oluşturma / güncelleme
     */
    public static function threadSave(ThreadRequest $request)
    {
        $user = auth()->user();

        if ($request->id)
        {
            $thread = Message::where('id', $request->id)->whereNull('message_id')->firstOrFail();

            if (!$thread->authority())
            {
                return abort(403);
            }

            $thread->subject = $request->subject;
            $thread->body = $request->body;
            $thread->updated_user_id = $user->id;
            $thread->save();
        }
        else
        {
            $thread = new Message;
            $thread->subject = $request->subject;
            $thread->body = $request->body;
            $thread->category_id = $request->category_id;
            $thread->user_id = $user->id;

            if (!$user->badge(2))
            {
                $user->addBadge(2); // ilk konu
            }

            if ($request->question)
            {
                $thread->question = 'unsolved';
            }

            $thread->save();

            Follow::create([ 'user_id' => $user->id, 'message_id' => $thread->id ]);
        }

        return [
            'status' => 'ok',
            'data' => [
                'route' => $thread->route()
            ]
        ];
    }

    /**
     * kategoriler
     */
    public static function categoryJson()
    {
        return [
            'status' => 'ok',
            'hits' => array_map(function($item) {
                return array_merge($item, [ 'url' => route('forum.category', $item['slug']) ]);
            }, Category::orderBy('sort')->get()->toArray())
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin get forum kategori
    # 
    public static function categoryGet(IdRequest $request)
    {
        $data = Category::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin update forum kategori
    # 
    public static function categoryUpdate(UpdateRequest $request)
    {
        $query = Category::where('id', $request->id)->firstOrFail();
        $query->fill($request->all());
        $query->save();

        return [
            'status' => 'ok',
            'data' => array_merge($query->toArray(), [ 'url' => route('forum.category', $query->slug) ])
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create forum kategori
    # 
    public static function categoryCreate(CreateRequest $request)
    {
        $query = new Category;
        $query->fill($request->all());
        $query->sort = intval(Category::orderBy('sort', 'DESC')->take(1)->value('sort'))+1;
        $query->save();

        return [
            'status' => 'ok',
            'data' => array_merge($query->toArray(), [ 'url' => route('forum.category', $query->slug) ])
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin delete forum kategori
    # 
    public static function categoryDelete(IdRequest $request)
    {
        $category = Category::where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $category->id
            ]
        ];

        $category->delete();

        return $arr;
    }

    /*******************************************************************************/

    /**
     * forum konu sayfası
     */
    public static function thread(string $slug, string $fake_slug, int $id)
    {
        $thread = Message::where('id', $id)->whereNull('message_id')->firstOrFail();

        if ($thread->category->slug != $slug)
        {
            return redirect($thread->route());
        }

        $thread->timestamps = false;
        $thread->hit = $thread->hit + 1;
        $thread->save();

        $messages = Message::where(function($query) use ($id) {
            $query->orWhere('id', $id);
            $query->orWhere('message_id', $id);
        })->orderBy('id', 'ASC')->paginate(10);

        return view('forum.thread', compact('thread', 'messages'));
    }

    /**
     * forum konu durumu
     */
    public static function threadStatus(IdRequest $request)
    {
        $thread = Message::where('id', $request->id)->whereNull('message_id')->firstOrFail();

        if (!$thread->authority(false))
        {
            return abort(403);
        }

        $thread->closed = $thread->closed ? false : true;
        $thread->save();

        UserActivityUtility::push(
            $thread->closed ? 'Konunuz Kapandı' : 'Konunuz Açıldı',
            [
                'key'       => implode('-', [ 'thread', 'status', $thread->id ]),
                'icon'      => $thread->closed ? 'lock' : 'lock_open',
                'markdown'  => '"'.$thread->subject.'" başlıklı konunuz, ['.auth()->user()->name.']('.route('user.profile', auth()->user()->id).') tarafından '.($thread->closed ? 'kapatıldı' : 'açıldı').'.',
                'user_id'   => $thread->user_id,
                'push'      => true,
                'button'    => [
                    'action' => $thread->route(),
                    'text'   => 'Konuya Git',
                    'class'  => 'btn-flat waves-effect'
                ]
            ]
        );

        return [
            'status' => 'ok',
            'data' => [
                'id' => $thread->id,
                'status' => $thread->closed ? 'closed' : 'open'
            ]
        ];
    }

    /**
     * forum konu sabitliği
     */
    public static function threadStatic(IdRequest $request)
    {
        $thread = Message::where('id', $request->id)->whereNull('message_id')->firstOrFail();

        if (!$thread->authority(false))
        {
            return abort(403);
        }

        $thread->static = $thread->static ? false : true;
        $thread->save();

        UserActivityUtility::push(
            $thread->static ? 'Konunuz Sabitlendi' : 'Konunuzun Sabitliği Kaldırıldı',
            [
                'key'       => implode('-', [ 'thread', 'static', $thread->id ]),
                'icon'      => 'terrain',
                'markdown'  => '['.auth()->user()->name.']('.route('user.profile', auth()->user()->id).'), "'.$thread->subject.'" başlıklı '.($thread->static ? 'konunuzu sabitledi' : 'konunuzun sabitliği kaldırdı').'.',
                'user_id'   => $thread->user_id,
                'push'      => true,
                'button'    => [
                    'action' => $thread->route(),
                    'text'   => 'Konuya Git',
                    'class'  => 'btn-flat waves-effect'
                ]
            ]
        );

        return [
            'status' => 'ok',
            'data' => [
                'id' => $thread->id,
                'status' => $thread->static ? 'static' : 'unstatic'
            ]
        ];
    }

    /**
     * forum mesaj sil
     */
    public static function messageDelete(IdRequest $request)
    {
        $message = Message::where('id', $request->id)->firstOrFail();

        if (!$message->authority(false))
        {
            return abort(403);
        }

        if ($message->message_id)
        {
            $subject = $message->thread->subject;

            if ($message->thread->question)
            {
                $message->thread->update([ 'question' => 'unsolved' ]);
            }
        }
        else
        {
            $subject = $message->subject;
        }

        UserActivityUtility::push(
            $message->message_id ? 'Verdiğiniz Bir Cevap Silindi' : 'Açtığınız Bir Konu Silindi',
            [
                'key'       => implode('-', [ 'message', 'delete', $message->id ]),
                'icon'      => 'delete',
                'markdown'  => '['.auth()->user()->name.']('.route('user.profile', auth()->user()->id).'), "'.($subject).'" başlıklı '.($message->message_id ? 'konuya verdiğiniz bir cevabı' : 'konunuzu').' sildi.',
                'user_id'   => $message->user_id,
                'push'      => true
            ]
        );

        $arr = [
            'status' => 'ok',
            'data' => [
                'status' => $message->message_id ? 'reply' : 'thread'
            ]
        ];

        $message->delete();

        return $arr;
    }

    /**
     * forum konu taşı
     */
    public static function threadMove(MoveRequest $request)
    {
        $thread = Message::where('id', $request->id)->whereNull('message_id')->firstOrFail();
        $category = Category::where('id', $request->category_id)->first();

        if ($thread->category_id == $category->id)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'xxx' => [ 'Konu zaten seçtiğiniz kategoride.' ]
                    ]
                ],
                422
            );
        }

        if (!$thread->authority(false))
        {
            return abort(403);
        }

        $user = auth()->user();

        $data[] = '| Durum              | Sonuç                                                                               |';
        $data[] = '| -----------------: |:----------------------------------------------------------------------------------- |';
        $data[] = '| Taşınan Konu       | ['.$thread->subject.']('.$thread->route().')                                        |';
        $data[] = '| Taşıyan            | ['.$user->name.']('.route('user.profile', $user->id).')                             |';
        $data[] = '| Eski Kategori      | ['.$thread->category->name.']('.route('forum.category', $thread->category->slug).') |';
        $data[] = '| Taşındığı Kategori | ['.$category->name.']('.route('forum.category', $category->slug).')                 |';

        $thread->category_id = $category->id;
        $thread->save();

        UserActivityUtility::push(
            'Konunuz Taşındı',
            [
                'key'       => implode('-', [ 'thread', 'move', $thread->id ]),
                'icon'      => 'drag_handle',
                'markdown'  => implode(PHP_EOL, $data),
                'user_id'   => $thread->user_id,
                'push'      => true,
                'button'    => [
                    'action' => $thread->route(),
                    'text'   => 'Konuya Git',
                    'class'  => 'btn-flat waves-effect'
                ]
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    /**
     * forum konu takibi
     */
    public static function threadFollow(IdRequest $request)
    {
        $user = auth()->user();

        $thread = Message::where('id', $request->id)->whereNull('message_id')->firstOrFail();

        $follow = $thread->followers()->where('user_id', $user->id);

        if (@$follow->exists())
        {
            $follow->delete();

            $status = 'unfollow';
        }
        else
        {
            Follow::create([ 'user_id' => $user->id, 'message_id' => $thread->id ]);

            $status = 'follow';
        }

        return [
            'status' => 'ok',
            'data' => [
                'status' => $status
            ]
        ];
    }

    /**
     * forum en iyi cevap
     */
    public static function messageBestAnswer(IdRequest $request)
    {
        $message = Message::where('id', $request->id)->whereNotNull('message_id')->firstOrFail();
        $thread = $message->thread;

        if (!$thread->question)
        {
            return abort(404);
        }

        if (!$thread->authority())
        {
            return abort(403);
        }

        Message::where('message_id', $message->message_id)->update([ 'question' => null ]);

        $thread->update([ 'question' => 'solved' ]);
        $message->update([ 'question' => 'check' ]);

        $user = auth()->user();

        $data[] = '| Durum              | Sonuç                                                                               |';
        $data[] = '| -----------------: |:----------------------------------------------------------------------------------- |';
        $data[] = '| İlgili Konu        | ['.$thread->subject.']('.$thread->route().')                      |';
        $data[] = '| Seçimi Yapan       | ['.$user->name.']('.route('user.profile', $user->id).')                             |';

        UserActivityUtility::push(
            'En İyi Cevap Seçildi',
            [
                'key'       => implode('-', [ 'thread', 'best', $message->message_id ]),
                'icon'      => 'email',
                'markdown'  => implode(PHP_EOL, $data),
                'user_id'   => $message->user_id,
                'push'      => true,
                'button'    => [
                    'action' => $thread->route(),
                    'text'   => 'Konuya Git',
                    'class'  => 'btn-flat waves-effect'
                ]
            ]
        );

        if ($message->user->notification('forum'))
        {
            $message->user->notify((new MessageNotification('Olive: 🌠🌟✨ En İyi Cevap ✨🌟🌠', 'Mesajınız en iyi cevap seçildi.', implode(PHP_EOL, $data)))->onQueue('email'));
        }

        if (!$message->user->badge(4))
        {
            $message->user->addBadge(4); // en iyi cevap
        }

        return [
            'status' => 'ok',
            'data' => [
                'id' => $message->id,
                'thread_id' => $message->message_id
            ]
        ];
    }

    /**
     * forum mesaj puanlama
     */
    public static function messageVote(VoteRequest $request)
    {
        $message = Message::where('id', $request->id)->firstOrFail();

        /*
        $cookie_key = implode('-', [ 'forum', 'message', $request->id ]);

        if (!Cookie::get($cookie_key))
        {
            Cookie::queue($cookie_key, $request->type, 60);
        }
        */

        $message->timestamps = false;

        switch ($request->type)
        {
            case 'neg': $message->decrement('vote'); break;
            case 'pos': $message->increment('vote'); break;
        }

        return [
            'status' => 'ok',
            'data' => [
                'id' => $message->id,
                'vote' => $message->vote
            ]
        ];
    }

    /**
     * forum mesaj puanlama
     */
    public static function messageSpam(IdRequest $request)
    {
        $message = Message::where('id', $request->id)->firstOrFail();

        $message->timestamps = false;
        $message->increment('spam');

        return [
            'status' => 'ok'
        ];
    }
}
