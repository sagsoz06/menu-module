<?php

namespace Modules\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Traits\CanGetSidebarClassForModule;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Menu\Blade\MenuDirective;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\Menuitem;
use Modules\Menu\Events\Handlers\ClearCache;
use Modules\Menu\Events\Handlers\RegisterMenuSidebar;
use Modules\Menu\Repositories\Cache\CacheMenuDecorator;
use Modules\Menu\Repositories\Cache\CacheMenuItemDecorator;
use Modules\Menu\Repositories\Eloquent\EloquentMenuItemRepository;
use Modules\Menu\Repositories\Eloquent\EloquentMenuRepository;
use Modules\Menu\Repositories\MenuItemRepository;
use Modules\Menu\Repositories\MenuRepository;
use Nwidart\Menus\MenuBuilder as Builder;
use Nwidart\Menus\Facades\Menu as MenuFacade;
use Nwidart\Menus\MenuItem as PingpongMenuItem;

class MenuServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration, CanGetSidebarClassForModule;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();

        $this->app->bind('menu.menu.directive', function () {
            return new MenuDirective();
        });

        $this->app['events']->listen(
            BuildingSidebar::class,
            $this->getSidebarClassForModule('menu', RegisterMenuSidebar::class)
        );

        \Widget::register('menuItems', 'Modules\Menu\Widgets\MenuWidgets@getItemsByMenu');
    }

    /**
     * Register all online menus on the Pingpong/Menu package
     */
    public function boot()
    {
        $this->registerMenus();
        $this->registerBladeTags();
        $this->publishConfig('menu', 'permissions');
        $this->publishConfig('menu', 'config');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    /**
     * Register class binding
     */
    private function registerBindings()
    {
        $this->app->bind(MenuRepository::class, function () {
                $repository = new EloquentMenuRepository(new Menu());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new CacheMenuDecorator($repository);
            }
        );

        $this->app->bind(MenuItemRepository::class, function () {
                $repository = new EloquentMenuItemRepository(new Menuitem());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new CacheMenuItemDecorator($repository);
            }
        );

        $this->app->events->subscribe(new ClearCache());
    }

    /**
     * Add a menu item to the menu
     * @param Menuitem $item
     * @param Builder $menu
     */
    public function addItemToMenu(Menuitem $item, Builder $menu)
    {
        if ($this->hasChildren($item)) {
            $localisedUri = ltrim(parse_url(\LaravelLocalization::localizeURL($item->uri), PHP_URL_PATH), '/');
            $target = $item->link_type != 'external' ? $localisedUri : $item->url;
            $this->addChildrenToMenu($item->title, $target, $item->items, $menu, ['icon' => $item->icon, 'target' => $item->target]);
        } else {
            $localisedUri = ltrim(parse_url(\LaravelLocalization::localizeURL($item->uri), PHP_URL_PATH), '/');
            $target = $item->link_type != 'external' ? $localisedUri : $item->url;
            $menu->url(
                $target,
                $item->title,
                [
                    'target' => $item->target,
                    'icon' => $item->icon,
                    'class' => $item->class,
                ]
            );
            //TopBar divider
            if($menu->getName()) {
                if(strpos($item->class, 'last') === false) {
                    $menu->divider();
                }
            }
        }
    }

    /**
     * Add children to menu under the give name
     *
     * @param string $name
     * @param object $children
     * @param Builder|MenuItem $menu
     */
    private function addChildrenToMenu($name, $url, $children, $menu, $attribs = [])
    {
        $menu->dropdown($name, $url, function (PingpongMenuItem $subMenu) use ($children) {
            foreach ($children as $child) {
                $this->addSubItemToMenu($child, $subMenu);
            }
        }, 0, $attribs);
    }

    /**
     * Add children to the given menu recursively
     * @param Menuitem $child
     * @param PingpongMenuItem $sub
     */
    private function addSubItemToMenu(Menuitem $child, PingpongMenuItem $sub)
    {
        if ($this->hasChildren($child)) {
            $localisedUri = ltrim(parse_url(\LaravelLocalization::localizeURL($child->uri), PHP_URL_PATH), '/');
            $target = $child->link_type != 'external' ? $localisedUri : $child->url;
            $this->addChildrenToMenu($child->title, $target, $child->items, $sub);
        } else {
            $localisedUri = ltrim(parse_url(\LaravelLocalization::localizeURL($child->uri), PHP_URL_PATH), '/');
            $target = $child->link_type != 'external' ? $localisedUri : $child->url;
            $sub->url($target, $child->title, 0, ['icon' => $child->icon, 'target' => $child->target, 'class' => $child->class]);
        }
    }

    /**
     * Check if the given menu item has children
     *
     * @param  object $item
     * @return bool
     */
    private function hasChildren($item)
    {
        return $item->items->count() > 0;
    }

    /**
     * Register the active menus
     */
    private function registerMenus()
    {
        if ($this->app['asgard.isInstalled'] === false ||
            $this->app['asgard.onBackend'] === true ||
            $this->app->runningInConsole() === true
        ) {
            return;
        }

        $menu = $this->app->make(MenuRepository::class);
        $menuItem = $this->app->make(MenuItemRepository::class);
        foreach ($menu->allOnline() as $menu) {
            $menuTree = $menuItem->getTreeForMenu($menu->id);
            MenuFacade::create($menu->name, function (Builder $menu) use ($menuTree) {
                foreach ($menuTree as $menuItem) {
                    $this->addItemToMenu($menuItem, $menu);
                }
            });
        }
    }

    /**
     * Register menu blade tags
     */
    protected function registerBladeTags()
    {
        if (app()->environment() === 'testing') {
            return;
        }

        $this->app['blade.compiler']->directive('menu', function ($arguments) {
            return "<?php echo MenuDirective::show([$arguments]); ?>";
        });
    }
}
