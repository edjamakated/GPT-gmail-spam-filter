<?php

$API_KEY = "<YOUR_API_KEY>";
$API_ENDPOINT = "https://api.openai.com/v1/chat/completions";

function generate_chat_completion($messages, $model = "gpt-4", $temperature = 1, $max_tokens = null) {
    global $API_KEY, $API_ENDPOINT;

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer $API_KEY"
    ];

    $data = [
        "model" => $model,
        "messages" => $messages,
        "temperature" => $temperature
    ];

    if ($max_tokens !== null) {
        $data["max_tokens"] = $max_tokens;
    }

    $options = [
        "http" => [
            "header" => $headers,
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($API_ENDPOINT, false, $context);

    if ($response === false) {
        throw new Exception("Error: Unable to get a response from the API.");
    }

    $response_data = json_decode($response, true);

    if (isset($response_data["choices"][0]["message"]["content"])) {
        return $response_data["choices"][0]["message"]["content"];
    } else {
        throw new Exception("Error: Unable to parse the API response.");
    }
}

$messages = [
    ["role" => "system", "content" => "You receive data about an e-mail message and respond TRUE or FALSE based on your opinion of it is a spam or phishing message. Don't respond with anything besides the word TRUE or FALSE. Remember, only respond with one of those two words."],
    ["role" => "user", "content" => "This text should be stored in a variable and accepted as a GET by this script"]
];

$response_text = generate_chat_completion($messages);
echo $response_text;

?>
