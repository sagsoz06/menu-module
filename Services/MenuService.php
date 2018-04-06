<?php
/**
 * Created by PhpStorm.
 * User: visualturk
 * Date: 07/04/2018
 * Time: 02:42
 */

namespace Modules\Menu\Services;


use Modules\Menu\Repositories\MenuRepository;

class MenuService
{
    /**
     * @var MenuRepository
     */
    private $menu;
    private $all;

    public function __construct(MenuRepository $menu)
    {
        $this->menu = $menu;
        $this->all = $this->menu->all();
    }

    public function title($slug="") {
        if($menu = $this->getAll()->where('name', $slug)->first()) {
            return $menu->title;
        }
        return false;
    }

    private function getAll() {
        return $this->all;
    }
}