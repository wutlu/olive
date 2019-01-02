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

use App\Http\Requests\Forum\PreviewRequest;
use App\Http\Requests\Forum\MoveRequest;
use App\Http\Requests\Forum\VoteRequest;
use App\Http\Requests\Forum\ThreadRequest;
use App\Http\Requests\Forum\ReplyRequest;

use App\Utilities\UserActivityUtility;

use Validator;
use Cookie;
use Term;

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
            'messagePreview'
        ]);

        $this->middleware('throttle:10,1')->only([
            'messageVote',
            'messageSpam'
        ]);

        $this->middleware('throttle:5,10')->only([
            'threadSave'
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
     * forum cevap ekleme
     */
    public static function replySave(ReplyRequest $request)
    {
        $user = auth()->user();

        $reply = Message::where('id', $request->reply_id)->firstOrFail();

        $thread = $reply->thread ? $reply->thread : $reply;
        $thread->updated_at = date('Y-m-d H:i:s');
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
     * forum mesaj önizleme
     */
    public static function messagePreview(PreviewRequest $request)
    {
        return [
            'status' => 'ok',
            'data' => [
                'message' => $request->body ? Term::markdown($request->body) : 'Önizleme Yok'
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
                'markdown'  => '"'.$thread->subject.'" başlıklı konunuz, '.auth()->user()->name.' tarafından '.($thread->closed ? 'kapatıldı' : 'açıldı').'.',
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
                'markdown'  => auth()->user()->name.', "'.$thread->subject.'" başlıklı '.($thread->static ? 'konunuzu sabitledi' : 'konunuzun sabitliği kaldırdı').'.',
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
                'markdown'  => auth()->user()->name.', "'.($subject).'" başlıklı '.($message->message_id ? 'konuya verdiğiniz bir cevabı' : 'konunuzu').' sildi.',
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

        if (!$message->thread->authority())
        {
            return abort(403);
        }

        Message::where('message_id', $message->message_id)->update([ 'question' => null ]);

        $message->thread->update([ 'question' => 'solved' ]);
        $message->update([ 'question' => 'check' ]);

        $user = auth()->user();

        $data[] = '| Durum              | Sonuç                                                                               |';
        $data[] = '| -----------------: |:----------------------------------------------------------------------------------- |';
        $data[] = '| İlgili Konu        | ['.$message->thread->subject.']('.$message->thread->route().')                      |';
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
                    'action' => $message->thread->route(),
                    'text'   => 'Konuya Git',
                    'class'  => 'btn-flat waves-effect'
                ]
            ]
        );

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
