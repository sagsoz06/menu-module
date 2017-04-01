<?php

namespace Modules\Menu\Http\Requests;

use Modules\Core\Internationalisation\BaseFormRequest;

class UpdateMenuRequest extends BaseFormRequest
{
    public function rules()
    {
        $menu = $this->route()->parameter('menu');

        return [
            'name' => 'required',
            'primary' => "unique:menu__menus,primary,{$menu->id}",
        ];
    }

    public function translationRules()
    {
        return [
            'title' => 'required',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'name.required' => trans('menu::validation.name is required'),
            'primary.unique' => trans('menu::validation.only one primary menu'),
        ];
    }
}
