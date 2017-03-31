<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Menu\Repositories\MenuItemRepository;
use Modules\Menu\Repositories\MenuRepository;
use Modules\Page\Repositories\PageRepository;

class MenuDatabaseSeeder extends Seeder
{
    private $menu;
    private $menuItem;
    private $page;

    public function __construct(MenuRepository $menu, MenuItemRepository $menuItem, PageRepository $page)
    {
        $this->menu = $menu;
        $this->page = $page;
        $this->menuItem = $menuItem;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data = [
            'name' => 'header',
            'tr'   => [
                'title'  => 'Üst Menü',
                'locale' => 'tr',
                'status' => 1
            ],
            'en'   => [
                'title'  => 'Header Menu',
                'locale' => 'en',
                'status' => 1
            ]
        ];

        $menu = $this->menu->create($data);
        $menuItem = $this->menuItem->allRootsForMenu($menu->id);

        $page = $this->page->findHomepage();

        $data = [
            'menu_id'   => $menu->id,
            'page_id'   => $page->id,
            'position'  => 0,
            'target'    => '_self',
            'link_type' => 'page',
            'parent_id' => $menuItem[0]->id,
            'is_root'   => 0,
            'tr'        => [
                'locale' => 'tr',
                'status' => 1,
                'title'  => 'Anasayfa',
                'uri'    => 'anasayfa'
            ],
            'en'        => [
                'locale' => 'en',
                'status' => 1,
                'title'  => 'Homepage',
                'uri'    => 'home'
            ]
        ];

        $this->menuItem->create($data);

        $data = [
            'menu_id'   => $menu->id,
            'position'  => 0,
            'target'    => '_self',
            'link_type' => 'internal',
            'parent_id' => $menuItem[0]->id,
            'is_root'   => 0,
            'tr'        => [
                'locale' => 'tr',
                'status' => 1,
                'title'  => 'İletişim',
                'uri'    => 'iletisim'
            ],
            'en'        => [
                'locale' => 'en',
                'status' => 1,
                'title'  => 'Contact',
                'uri'    => 'contact'
            ]
        ];

        $this->menuItem->create($data);

        $data = [
            'name' => 'footer',
            'tr'   => [
                'title'  => 'Alt Menü',
                'locale' => 'tr',
                'status' => 1
            ],
            'en'   => [
                'title'  => 'Footer Menu',
                'locale' => 'en',
                'status' => 1
            ]
        ];

        $this->menu->create($data);
    }
}
