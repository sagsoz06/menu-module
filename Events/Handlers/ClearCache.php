<?php namespace Modules\Menu\Events\Handlers;


use Illuminate\Events\Dispatcher;
use Modules\Menu\Repositories\MenuItemRepository;

class ClearCache
{
    public function clear()
    {
        return app(MenuItemRepository::class)->clearCache();
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen('menu.clearCache', '\Modules\Menu\Events\Handlers\ClearCache@clear');
    }
}