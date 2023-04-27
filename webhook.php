<?php
// Get the POST data from the Gmail API
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

// Extract the message ID from the data
$messageId = $data['message']['id'];

// Load the Gmail API
require_once __DIR__ . '/vendor/autoload.php'; // Replace with your own path
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json'); // Replace with your own path
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Gmail::GMAIL_READONLY);
$service = new Google_Service_Gmail($client);

// Get the message data from the Gmail API
$message = $service->users_messages->get('me', $messageId);

// Extract the email headers and metadata
$headers = $message->getPayload()->getHeaders();
$subject = $message->getPayload()->getHeadersByName('Subject')[0]->getValue();
$from = $message->getPayload()->getHeadersByName('From')[0]->getValue();
$to = $message->getPayload()->getHeadersByName('To')[0]->getValue();
$date = $message->getPayload()->getHeadersByName('Date')[0]->getValue();
$body = $message->getPayload()->getBody()->getData();
$spamScore = $message->getPayload()->getHeadersByName('X-Spam-Score');
$phishingScore = $message->getPayload()->getHeadersByName('X-PhishingScore');
$isPhishing = $message->getPayload()->getHeadersByName('X-Phishing');
$isSpam = $message->getPayload()->getHeadersByName('X-Spam-Flag');
$isSuspicious = $message->getPayload()->getHeadersByName('X-Suspicious');

// Process the email data
$emailData = [
  'subject' => $subject,
  'from' => $from,
  'to' => $to,
  'date' => $date,
  'body' => base64_decode(strtr($body, '-_', '+/')),
  'spamScore' => $spamScore ? floatval($spamScore[0]->getValue()) : 0,
  'phishingScore' => $phishingScore ? floatval($phishingScore[0]->getValue()) : 0,
  'isPhishing' => $isPhishing ? true : false,
  'isSpam' => $isSpam ? true : false,
  'isSuspicious' => $isSuspicious ? true : false
];

// Send the email data to your endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'index.php'); // Replace with your own endpoint URL
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
?>
