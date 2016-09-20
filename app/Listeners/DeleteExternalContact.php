<?php

namespace App\Listeners;

use App\Events\ContactDeleteEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteExternalContact
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ContactDeleteEvent  $event
     * @return void
     */
    public function handle(ContactDeleteEvent $event)
    {
        //
    }
}
