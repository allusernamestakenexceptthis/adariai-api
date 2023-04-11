<?php
/**
 * This is a sample PHP script that shows how to use the OpenAI API
 * to get the API key from the environment variable or from the php.ini file
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
$promptText = "what's the capital of burkino faso?";

$messagesInput = [
    [
        "role" => "system",
        "content" => "You are a helpful assistant."
    ],
    [
        "role" => "assistant", 
        "content" => "The capital of Burkina Faso is Ouagadougou."
    ],
    [
        "role" => "user",
        "content" => "What city or town lies to the east of that city?"
    ],
];


//you can set the settings here, see other examples
$settings = [];

require_once '../vendor/autoload.php';
use AdariaiApi\Wrapper;

// Get the API key from the environment variable or from the php.ini file
$api_key = getenv('OPENAI_API_KEY')?
                getenv('OPENAI_API_KEY'):
                get_cfg_var('OPENAI_API_KEY');

// Create a new instance of the Wrapper class
$openapi = new Wrapper($api_key);

$response = null;
try {
    $response = $openapi->getChat($model, $messagesInput);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Return output
$output = [
   'response' => $response
];
echo json_encode($output);