<?php

namespace App\Listeners;

use App\Events\ContactDeleteEvent;
use App\ExternalContactRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteExternalContact implements ShouldQueue
{

    public $syncRepo;
    public $doSync;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ExternalContactRepositoryInterface $syncRepo)
    {
        $this->syncRepo = $syncRepo;
        $this->doSync = config('services.do_sync');
    }

    /**
     * Handle the event.
     *
     * @param  ContactDeleteEvent  $event
     * @return void
     */
    public function handle(ContactDeleteEvent $event)
    {
        if ($this->doSync) {
            $externalId = $event->contact->getExternalId();
            if ($externalId) {
                $this->syncRepo->delete($externalId);
            }
        }
    }
}
