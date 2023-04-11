<?php
/**
 * This is a sample PHP script that shows how to use the OpenAI API
 * to get the API key from the environment variable or from the php.ini file
 * this example shows how to use the stream feature
 *
 * PHP version 7.4.3
 *
 * @category OpenAI
 * @package  ComGomilkywayOpenai
 * @author   Ari Adari <admin@ariadari.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @version  GIT:1.0.0
 * @link     http://aridadari.com http://gomilkyway.com
 */
/*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the MIT License as published by
* the Open Source Initiative.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* MIT License for more details.
*
* You should have received a copy of the MIT License
* along with this program.  If not, see <https://opensource.org/licenses/MIT>.
*/


$model = "gpt-3.5-turbo";
$promptText = 'Could you tell me a short story?';

$settings = [ //optional
    'temperature' => 1.0,
    //'max_tokens' => 150,
    'top_p' => 1,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
    'stop' => null
];

require_once '../vendor/autoload.php';
use AdariaiApi\Wrapper;


// Get the API key from the environment variable or from the php.ini file
$api_key = getenv('OPENAI_API_KEY')?
                getenv('OPENAI_API_KEY'):
                get_cfg_var('OPENAI_API_KEY');

// Create a new instance of the Wrapper class
$openapi = new Wrapper($api_key);

$openapi->setToStream(true);

//Set php to stream output to client
ini_set('output_buffering', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level()) {
    ob_end_clean();
}
ob_implicit_flush(1);


try {
    $stream = $openapi->getChat($model, $promptText, $settings);
    
    $headerSent = false;
    while (!$stream->eof()) {
        if (!$headerSent) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            $headerSent = true;
        }
        $chunk = $stream->read(1024);
        echo "$chunk";
        flush();
    }
    
} catch (Exception $e) {
    //var_dump($openapi->getLastResponseHeaders());
    echo json_encode(['error' => $e->getMessage()]);
}
