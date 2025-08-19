<?php

namespace Modules\Imedia\Http\Requests;

use Imagina\Icore\Http\Request\CoreFormRequest;
use Illuminate\Contracts\Validation\Validator;

class CreateFileRequest extends CoreFormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:2000|mimes:jpg,png,jpeg,webp,pdf,doc,docx,xls,xlsx,svg,mp4,webn,ogg,mp3,avi'
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
