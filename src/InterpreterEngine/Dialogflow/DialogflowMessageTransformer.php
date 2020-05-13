<?php

namespace OpenDialogAi\InterpreterEngine\Dialogflow;

use Google\Cloud\Dialogflow\V2\Intent\Message;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;

class DialogflowMessageTransformer
{
    public static function interpretMessages(Message $responseMessage)
    {
        $messageGenerator = new MessageMarkUpGenerator();

        if ($simpleResponses = $responseMessage->getSimpleResponses()) {
            foreach ($simpleResponses->getSimpleResponses() as $simpleResponse) {
                $messageText = $simpleResponse->getTextToSpeech();
                $messageGenerator->addTextMessage($messageText);
            }
        }

        if ($basicCard = $responseMessage->getBasicCard()) {
            $title = $basicCard->getTitle();
            $subtitle = $basicCard->getSubtitle();
            $text = $basicCard->getFormattedText();

            $image = [];
            if ($cardImage = $basicCard->getImage()) {
                $imageSrc = $cardImage->getImageUri();

                $image = [
                    'src' => $imageSrc,
                    'url' => '',
                    'new_tab' => true,
                ];
            }

            $buttons = [];
            foreach ($basicCard->getButtons() as $button) {
                $buttonText = $button->getTitle();
                $buttonLink = $button->getOpenUriAction()->getUri();

                $buttons[] = [
                    'text' => $buttonText,
                    'link' => $buttonLink,
                    'link_new_tab' => true,
                ];
            }

            $messageGenerator->addRichMessage($title, $subtitle, $text, $buttons, $image);
        }

        return $messageGenerator->getMessagesMarkUp();
    }
}
