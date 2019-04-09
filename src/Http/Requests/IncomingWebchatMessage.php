<?php

namespace OpenDialogAi\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomingWebchatMessage extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'notification' => 'required|string|in:message,chat_open,trigger',
            'user_id' => 'required|string',
            'author' => 'required|string',
            // The content object is only required for regular messages.
            'content' => 'required_if:notification,==,message|array',
        ];
    }
}
