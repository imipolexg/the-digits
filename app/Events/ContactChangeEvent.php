<?php

namespace App\Events;

use App\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ContactChangeEvent
{
    use InteractsWithSockets, SerializesModels;

    public $contact;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Contact $contact, $userId)
    {
        $this->contact = $contact;
        $this->userId = $userId;
    }
}
