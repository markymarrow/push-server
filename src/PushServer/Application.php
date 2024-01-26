<?php

namespace PushServer;

use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;

/**
 * Represents an iOS App we can send push notifications to
 */

class Application {
    public readonly String $app_id;
    protected int $database_id;
    protected String $key;
    protected String $key_id;
    protected String $team_id;
    protected String $jwt;
    protected Carbon $creation_time;

    public function __construct(String $app_id)
    {
        $this->app_id = $app_id;
        $this->retrieve_details();
    }

    /**
     * Get Application's signing key and other auth details
     */
    protected function retrieve_details()
    {
        $stmt = $GLOBALS['pdoDatabase']->prepare("select * from application where app_id = ?");
        $stmt->execute([$this->app_id]);
        $app_record = $stmt->fetch();
        if (!$app_record) {
            throw new Exception("Couldn't find details for Application with app_id '{$this->app_id}'");
        }
        $this->database_id = $app_record['id'];
        $this->key = $app_record['key'];
        $this->key_id = $app_record['key_id']; // The Key ID of the p8 file (available at https://developer.apple.com/account/ios/certificate/key)
        $this->team_id = $app_record['team_id']; // The Team ID of your Apple Developer Account (available at https://developer.apple.com/account/#/membership/)
    }

    /**
     * Lookup existing or regenerate a JWT for the application
     */
    public function getJwt() : String
    {
        $now = Carbon::now();
        if (!isset($this->creation_time) || $now->diffInMinutes($this->creation_time) > 60) {
            $this->creation_time = $now;
            $payload = [
                "iss" => $this->team_id,
                "iat" => $this->creation_time->getTimestamp(),
            ];
            $this->jwt = JWT::encode($payload, $this->key, 'ES256');
        }
        return $this->jwt;
    }

    /**
     * Send a broadcast push notification to the whole subscriber base
     */
    public function sendToAllDevices(Notification $notification)
    {
        foreach ($this->getAllDevices() as $device) {
            $notification->PushTo($device, $this);
        }
    }

    /**
     * Gets all Devices (device tokens) that have the app installed and have given permission for notifications
     */
    public function getAllDevices()
    {
        //TODO:replace this with a database lookup
        $devices = [];
        foreach ($this->retrieveAllDevices() as $device_record) {
            $devices[] = new Device($device_record['device_token'], $this);
        }
        return $devices;
    }

    /**
     * Lookup all devices for this application in database
     */
    protected function retrieveAllDevices()
    {
        $stmt = $GLOBALS['pdoDatabase']->prepare("select * from device where fk_application = ?");
        $stmt->execute([$this->database_id]);
        while ($device_record = $stmt->fetch()) {
            yield $device_record;
        }

    }
}