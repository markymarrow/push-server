<?php

namespace PushServer;

use PushServer\Application;
use PushServer\Device;
use GuzzleHttp\Client;

/**
 * Holds the content of a Push Notification to be sent to a device
 */

class Notification {
    protected static ?Client $httpClient;
    protected static String $pushUrl = "https://api.development.push.apple.com/3/device/";

    protected String $title;
    protected String $body;

    public function __construct(String $title, String $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Send this notification to 'device' via 'application'
     */
    public function PushTo(Device $device, Application $application)
    {
        if (!isset($httpClient)) {
            $httpClient = new Client(['version' => 2]);
        }
        //TODO:Handle exceptions and other return responses
        $response = $httpClient->post(
            self::$pushUrl . $device->token,
            [
                'headers' => [
                    'Authorization' => "Bearer " . $application->getJwt(),
                    "apns-topic" => $application->app_id,
                    "apns-priority" => 10,
                    "apns-expiration" => time() + 1800
                ],
                'body' => $this->getPayload()
            ]
        );
    }

    protected function getPayload(): String
    {
        return json_encode(['title' => $this->title, 'body' => $this->body]);
    }
}