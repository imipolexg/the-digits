<?php

namespace App\Listeners;

use App\Events\ContactChangeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncExternalContact
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
     * @param  ContactChangeEvent  $event
     * @return void
     */
    public function handle(ContactChangeEvent $event)
    {
        //
    }
}
