<?php

namespace App\Messages;

use DateTimeInterface;

class SecretJsonMessages
{
    /** @var string */
    private $title;
    /** @var string */
    private $description;
    /** @var DateTimeInterface */
    private $date;

    public function __construct(string $title, string $description, DateTimeInterface $date)
    {
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }
}
