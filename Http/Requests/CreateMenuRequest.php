<?php

namespace Modules\Menu\Http\Requests;

use Modules\Core\Internationalisation\BaseFormRequest;

class CreateMenuRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'primary' => 'unique:menu__menus',
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
