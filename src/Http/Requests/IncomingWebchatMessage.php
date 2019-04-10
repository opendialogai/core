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
            'notification' => 'required|string|in:message',
            'user_id' => 'required|string',
            'author' => 'required|string',
            // The content array is required for all messages.
            'content' => 'required|array',
            // Validate the message type.
            'content.type' => 'in:chat_open,text,trigger',
            // The callback_id is required for chat_opens.
            'content.callback_id' => 'required_if:content.type,==,chat_open|string',
            // The user data array is required for chat_opens.
            'content.user' => 'required_if:content.type,==,chat_open|array',
        ];
    }
}
