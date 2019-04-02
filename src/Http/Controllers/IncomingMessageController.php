<?php

namespace OpenDialogAi\Core\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IncomingChatController extends BaseController
{
    use ValidatesRequests;

    public function receive(Request $request)
    {
        // Validate the request.
        $requestValid = $this->validateRequest($request);

        if ($requestValid !== true) {
            return response()->json($requestValid, 400);
        }

        // Handle requests without the content object.
        if (!$content = $request->input('content')) {
            $content = [0 => 0];
        }

        // Wrap single messages in an array.
        if (!isset($content[0])) {
            $content = [$content];
        }

        // Validate the data for regular messages.
        if ($request->input('notification') === 'message') {
            $contentValid = $this->validateContent($content);
            if ($contentValid !== true) return response()->json($contentValid, 400);
        }

        $messageType = $request->input('notification');
        $userId      = $request->input('user_id');
        $author      = $request->input('author');
        $messageId   = $request->input('message_id');

        // Log that the message was successfully received.
        Log::info("Webchat endpoint received a valid message of type ${messageType}.");
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    private function validateRequest($request)
    {
        Log::debug('Request:', $request->all());

        $validator = Validator::make($request->all(), [
            'notification' => 'required|string|in:read_receipt,typing_on,typing_off,message',
            'user_id' => 'required|string',
            'author' => 'required|string',
            // The message id is only required for read receipts.
            'message_id' => 'required_if:notification,==,read_receipt|string',
            // The content object is only required for regular messages.
            'content' => 'required_if:notification,==,message|array',
        ]);

        if ($validator->fails()) {
            $validationMessages = $validator->messages();
            Log::info('Webchat endpoint received an invalid message. Errors were: ' . $validationMessages);
            return $validationMessages;
        }

        return true;
    }

    /**
     * @param array $content
     *
     * @return mixed
     */
    private function validateContent($content)
    {
        // Validate the message(s) received.
        foreach ($content as $message) {
            // Validate the message format.
            $messageValidator = Validator::make($message, [
                'author' => 'required|string',
                'type' => 'required|string|in:chat_open,trigger,url_click,webchat_form_response,webchat_list_response,text,button,button_response,image,longtext,longtext_response,typing,read,system,longtext_response',
                'data' => 'required|array',
            ]);
            if ($messageValidator->fails()) {
                $validationMessages = $messageValidator->messages();
                Log::info("Webchat endpoint received an invalid message. Errors were: ${validationMessages}");
                return $validationMessages;
            }
        }
        return true;
    }
}
