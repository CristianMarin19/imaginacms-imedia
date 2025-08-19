<?php

namespace Modules\Imedia\Http\Requests;

use Imagina\Icore\Http\Request\CoreFormRequest;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Validation\Rule;
use Modules\Imedia\Rules\AlphaDashWithSpaces;

class CreateFolderRequest extends CoreFormRequest
{
    public function rules(): array
    {

        $parentId = $this->get('parent_id') ?? null;

        return [
            'name' => [
                new AlphaDashWithSpaces(),
                'required',
                Rule::unique('imedia__files', 'filename')->where(function ($query) use ($parentId) {
                    return $query->where('is_folder', 1)->where('folder_id', $parentId)->where('id', '!=', $this->id);
                }),
            ],
        ];
    }

    public function translationRules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [];
    }

    public function translationMessages(): array
    {
        return [];
    }

    public function getValidator(): Validator
    {
        return $this->getValidatorInstance();
    }
}
