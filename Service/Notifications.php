<?php

namespace RMS\PushNotificationsBundle\Service;

use RMS\PushNotificationsBundle\Message\MessageInterface;
use Monolog\Logger;

class Notifications
{
    /**
     * Array of handlers
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     */
    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }


    /**
     * Sends a message to a device, identified by
     * the OS and the supplied device token
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface $message
     * @throws \RuntimeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!isset($this->handlers[$message->getTargetOS()])) {
            throw new \RuntimeException("OS type {$message->getTargetOS()} not supported");
        }

        /** @var \RMS\PushNotificationsBundle\Service\OS\OSNotificationServiceInterface $handler */
        $handler = $this->handlers[$message->getTargetOS()];

        $result = $handler->send($message);

        if ($this->logger) {
            $this->logger->info($message->getMessage(), array(
                'message_body' => $message->getMessageBody(),
                'response' => serialize($handler->getResponses()),
                'result' => $result
            ));
        }

        return $result;
    }

    /**
     * Adds a handler
     *
     * @param $osType
     * @param $service
     */
    public function addHandler($osType, $service)
    {
        if (!isset($this->handlers[$osType])) {
            $this->handlers[$osType] = $service;
        }
    }
}
