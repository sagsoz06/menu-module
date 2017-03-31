<?php

namespace Modules\Menu\Http\Requests;

use Modules\Core\Internationalisation\BaseFormRequest;

class UpdateMenuItemRequest extends BaseFormRequest
{
    protected $translationsAttributesKey = 'menu::menu-items.validation.attributes';

    public function rules()
    {
        return [];
    }

    public function translationRules()
    {
        return [
            'title' => 'required'
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return trans('validation');
    }
}
