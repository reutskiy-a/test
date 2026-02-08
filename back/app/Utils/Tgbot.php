<?php

declare(strict_types=1);

namespace App\Utils;

class Tgbot
{
    public static function msg(array | string $message)
    {
        $token = '';
        $chatId = '';

        $apiDomain = 'https://api.telegram.org/bot' . $token . '/';
        $url = $apiDomain . 'sendMessage';

        if(is_array($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $params = array(
            'chat_id' => $chatId,
            'text' => date('[h:i:s] '). ' ' . $message,
            'parse_mode' => "html"
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // для ngrok, чтобы ошибки небыло
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    // для ngrok, чтобы ошибки небыло


        $response = curl_exec($ch);
        curl_close($ch);
        // d(curl_error($ch));

        $response = json_decode($response, true);
        return $response;
    }
}
