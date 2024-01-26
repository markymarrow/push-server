<?php

namespace PushServer;

use PushServer\Application;

/**
 * Represents an iOS device that has given permission for push notifications
 * (belongs to an Application)
 */

class Device {
    public readonly String $token;
    protected Application $application;

    public function __construct(String $device_token, Application $application)
    {
       $this->token = $device_token;
       $this->application = $application;
    }
}