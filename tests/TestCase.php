<?php

use App\Contact;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create();
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }


    /**
     * Should we skip integration tests?
     *
     * @return bool
     */
    public function skipIntegrations()
    {
        return strtolower(env('RUN_INTEGRATION_TESTS', 'no')) !== 'yes';
    }

    /**
     * Makes a contact with random data.
     *
     * @return Contact
     */
    public function makeRandomContact()
    {
        $contact = new Contact();

        $contact->setEmail($this->faker->unique()->email);
        $contact->setFirstName($this->faker->firstName);
        $contact->setLastName($this->faker->lastName);
        $contact->setPhone($this->faker->phoneNumber);

        return $contact;
    }
}
