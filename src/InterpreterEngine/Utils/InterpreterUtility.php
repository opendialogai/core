<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters\Utils;

class InterpreterUtility
{
    /**
     * @param $text
     * @return string
     */
    public static function formatTextMessageWithLinks($text)
    {
        $regex = '/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU';
        preg_match_all($regex, $text, $links, PREG_SET_ORDER);

        $responseText = $text;

        foreach ($links as $link) {
            $linkTag = $link[0];
            $linkUrl = $link[2];
            $linkText = $link[3];

            $linkMarkup = sprintf(
                '<link new_tab="true"><url>%s</url><text>%s</text></link>',
                $linkUrl,
                $linkText
            );

            $responseText = str_replace($linkTag, $linkMarkup, $responseText);
        }

        $responseText = '<text-message>' . $responseText . '</text-message>';

        return $responseText;
    }
}
