<?php namespace App;

use App\Contact;

/**
 * A basic repository interface for interacting with contact 'external'
 * collections that sync with a native repository.
 *
 * Does not support any predicate expressions for restricting the contacts
 * returned by getAll().
 *
 * The only thing that might be surprising is the semantics of "sync". Sync is
 * a create -or- update operation that will succeed whether or not the contact
 * exists. The result of a sync will always be a contact existing in the
 * repository the properties of the Contact object passed.
 *
 * TODO: Pagination?
 */

interface ExternalContactRepositoryInterface
{

    /**
     * Get a contact object by its id
     *
     * @param int $externalId
     *
     * @return Contact|null
     */
    public function get(int $externalId);

    /**
     * Get a contact object by its email address
     *
     * @param string $email
     *
     * @return Contact|null
     */
    public function getByEmail(string $email);

    /**
     * Get all contacts in the repository
     *
     * @return array
     */
    public function getAll();

    /**
     * Idempotently create or update a contact
     *
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function sync(Contact $contact);

    /**
     * Create a contact
     *
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function create(Contact $contact);

    /**
     * Update a contact
     *
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function update(Contact $contact);

    /**
     * Delete a contact
     *
     * @param int $externalId
     *
     * @return void
     */
    public function delete(int $externalId);
}
