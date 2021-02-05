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
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'notification' => 'required|string|in:message',
            'user_id' => 'required|string',
            'author' => 'required|string',
            // The content array is required for all messages.
            'content' => 'required|array',
            // Validate the message type.
            'content.type' => 'in:button_response,longtext_response,chat_open,text,trigger,url_click,form_response',
            // The callback_id is required for chat_opens.
            'content.callback_id' => 'required_if:content.type,in:button_response,chat_open,form_response|string',
            // The user array is required for chat_opens.
            'content.user' => 'required_if:content.type,==,chat_open|array',
        ];
    }
}
