<?php

namespace App\Listeners;

use App\ContactRepositoryInterface;
use App\Events\ContactChangeEvent;
use App\ExternalContactRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncExternalContact implements ShouldQueue
{

    public $syncRepo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ExternalContactRepositoryInterface $syncRepo, ContactRepositoryInterface $repo)
    {
        $this->syncRepo = $syncRepo;
        $this->repo = $repo;
        $this->doSync = config('services.do_sync');
    }

    /**
     * Handle the event.
     *
     * @param  ContactChangeEvent  $event
     * @return void
     */
    public function handle(ContactChangeEvent $event)
    {
        if ($this->doSync) {
            $syncedContact = $this->syncRepo->sync($event->contact);

            // Update the external id in the repository
            $this->repo->setUserId($event->userId);
            $this->repo->update($syncedContact);
        }
    }
}
