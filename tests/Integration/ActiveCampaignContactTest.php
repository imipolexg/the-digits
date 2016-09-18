<?php

use App\ActiveCampaign\ActiveCampaignContactRepo;
use App\Contact;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * Test contact CRUD
     *
     * Just basic CRUD tests to ensure the API connection works.
     *
     * No edge case tests or stress testing.
     *
     * @return void
     */
    public function testContactCRUD()
    {
        if ($this->skipIntegrations()) {
            return;
        }

        $repo = new ActiveCampaignContactRepo();
        $rando = $this->makeRandomContact();

        // Test create contact
        $created = $repo->create($rando);
        $rando->setExternalId($created->getExternalId());
        $this->assertEquals($created, $rando);

        // Test read of created
        $created2 = $repo->get($rando->getExternalId());
        $this->assertEquals($created2, $rando);

        // Test update contact
        $newRando = $this->makeRandomContact();
        $newRando->setEmail($rando->getEmail());
        $newRando->setExternalId($rando->getExternalId());
        $updated = $repo->update($newRando);
        $this->assertEquals($updated, $newRando);

        // Test read of updated
        $updated2 = $repo->get($newRando->getExternalId());
        $this->assertEquals($updated2, $newRando);

        // Test sync
        $newRando = $this->makeRandomContact();
        $newRando->setEmail($rando->getEmail());
        $synced = $repo->sync($newRando);
        $newRando->setExternalId($synced->getExternalId());

        // Test read of synced
        $synced2 = $repo->get($newRando->getExternalId());
        $this->assertEquals($synced2, $newRando);

        $repo->delete($created->getExternalId());
    }

    /**
     * Makes a contact with random data.
     *
     * @return Contact
     */
    public function makeRandomContact()
    {
        $contact = new Contact();

        $contact->setEmail(uniqid() . '@' . uniqid() . '.tld');
        $contact->setFirstName(uniqid());
        $contact->setLastName(uniqid());
        $contact->setPhone('555-555-5555');

        return $contact;
    }
}
