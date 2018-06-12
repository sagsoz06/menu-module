<?php namespace Modules\Menu\Widgets;


use Modules\Menu\Repositories\MenuItemRepository;
use Modules\Menu\Repositories\MenuRepository;

class MenuWidgets
{
    /**
     * @var MenuRepository
     */
    private $menu;
    /**
     * @var MenuItemRepository
     */
    private $menuItem;

    public function __construct(MenuRepository $menu, MenuItemRepository $menuItem)
    {
        $this->menu = $menu;
        $this->menuItem = $menuItem;
    }

    public function getItemsByMenu($slug="", $view="menu-items", $attributes=[])
    {
        if($menu = $this->menu->findBySlug($slug)) {
            $menuItems = $this->menuItem->rootsForMenu($menu->id);
            return view('menu::widgets.'.$view, compact('menu', 'menuItems', 'attributes'));
        }
        return null;
    }
}