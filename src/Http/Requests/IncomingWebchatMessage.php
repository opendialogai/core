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
            'notification' => 'required|string|in:read_receipt,typing_on,typing_off,message',
            'user_id' => 'required|string',
            'author' => 'required|string',
            // The message id is only required for read receipts.
            'message_id' => 'required_if:notification,==,read_receipt|string',
            // The content object is only required for regular messages.
            'content' => 'required_if:notification,==,message|array',
        ];
    }
}
