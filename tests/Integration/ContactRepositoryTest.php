<?php

use App\Contact;
use App\ContactRepository;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactRepositoryTest extends TestCase
{

    public function testRepoCRUD()
    {
        $random = $this->makeRandomContact();

        $repo = new ContactRepository();
        $repo->setUserId(1);
        $created = $repo->create($random);
        $random->setId($created->getId());

        $this->assertEquals($created, $random);

        $repo->delete($created->getId());
    }
}
