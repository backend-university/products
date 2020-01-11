<?php

namespace App\Messages;

use DateTimeInterface;

class DaemonLogMessage
{
    /** @var string */
    private $message;
    /** @var DateTimeInterface */
    private $date;

    public function __construct(string $description, DateTimeInterface $date)
    {
        $this->message = $description;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }
}
