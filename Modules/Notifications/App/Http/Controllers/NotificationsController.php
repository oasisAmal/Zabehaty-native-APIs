<?php

namespace Modules\Notifications\App\Http\Controllers;

use App\Enums\Pagination;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Notifications\App\Transformers\NotificationResource;

class NotificationsController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = auth('user')->user();
    }

    public function list(Request $request)
    {
        $result = $this->user->notifications()->orderBy('id', 'DESC')->paginate(Pagination::PER_PAGE)->appends($request->except(['page', '_token']));
        return responsePaginate(NotificationResource::collection($result));
    }

    public function read($id)
    {
        $this->user->notifications()->where('id', $id)->update(['read_at' => now()]);
        return responseSuccessMessage(__('messages.notification_read'));
    }

    public function readAll()
    {
        $this->user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        return responseSuccessMessage(__('messages.notification_read_all'));
    }

    public function unreadCount()
    {
        $count = $this->user->notifications()->whereNull('read_at')->count();
        return responseSuccessData($count);
    }
}
