<?php

namespace App;

use DB;
use App\Contact;
use App\ContactModel;
use App\ContactRepositoryInterface;
use Illuminate\Database\QueryException;

class ContactRepository implements ContactRepositoryInterface
{

    protected $userId;
    protected $externalSync;

    protected $propToGetSet = [
        'custom1'     => 'Custom1',
        'custom2'     => 'Custom2',
        'custom3'     => 'Custom3',
        'custom4'     => 'Custom4',
        'custom5'     => 'Custom5',
        'email'       => 'Email',
        'external_id' => 'ExternalId',
        'first_name'  => 'FirstName',
        'id'          => 'Id',
        'last_name'   => 'LastName',
        'phone'       => 'Phone',
    ];

    protected $searchColumns = [ 'custom1', 'custom2', 'custom3', 'custom4',
        'custom5', 'email', 'first_name', 'last_name', 'phone', ];

    /**
     * Dynamically constructs Contact entity from an Eloquent model
     *
     * @param ContactModel $model
     *
     * @return Contact
     */
    protected function makeContactFromModel(ContactModel $model)
    {
        $contact = new Contact();

        foreach ($this->propToGetSet as $prop => $getSet) {
            $setter = 'set' . $getSet;
            $contact->{$setter}($model->{$prop});
        }

        return $contact;
    }

    /**
     * Constructs an array of Contact entities from an array of ContactModels
     *
     * @param array $models
     *
     * @return array
     */
    protected function parseModelArray(array $models)
    {
        $contacts = [];
        foreach ($models as $model) {
            $contacts[] = $this->makeContactFromModel($model);
        }

        return $contacts;
    }

    /**
     * Modifies and saves an Eloquent Model from a Contact entity
     *
     * @param Contact $contact
     * @param ContactModel $model
     *
     * @return ContactModel
     */
    protected function saveModelFromContact(Contact $contact, ContactModel $model)
    {
        foreach ($this->propToGetSet as $prop => $getSet) {
            $getter = 'get' . $getSet;
            $model->{$prop} = $contact->{$getter}();
        }

        $model->user_id = $this->userId;

        try {
            $model->save();
        } catch (QueryException $ex) {
            // Integrity constraint vioation (uniqueness error)
            if ($ex->getCode() === '23000') {
                ContactRepoException::throwDuplicateException();
            }

            throw $ex;
        }

        return $model;
    }

    /**
     * Get a contact object by its id
     *
     * @param int $contactId
     *
     * @return Contact|null
     */
    public function get(int $contactId)
    {
        $model = ContactModel::where('id', $contactId)
            ->where('user_id', $this->userId)
            ->first();

        if (empty($model)) {
            ContactRepoException::throwNotFoundException();
        }

        return $this->makeContactFromModel($model);
    }

    /**
     * Get a contact object by its email address
     *
     * @param string $email
     *
     * @return Contact|null
     */
    public function getByEmail(string $email)
    {
        $model = ContactModel::where('email', $email)
            ->where('user_id', $this->userId)
            ->first();

        if (empty($model)) {
            ContactRepoException::throwNotFoundWithEmailException();
        }

        return $this->makeContactFromModel($model);
    }

    /**
     * Get all contacts in the repository
     *
     * @return array
     */
    public function getAll()
    {
        $models = ContactModel::where('user_id', $this->userId)
            ->orderBy('email', 'asc')->get()->all();

        return $this->parseModelArray($models);
    }

    /**
     * Search the repository against $needle
     *
     * @param string $needle
     *
     * @return array
     */
    public function search(string $needle)
    {
        $searchColumns = $this->searchColumns;

        // We need to quote the needle since it will be used directly in a raw query
        $needle = DB::getPdo()->quote('%' . $needle . '%');

        $search = ContactModel::where('user_id', $this->userId)
            ->where(function ($query) use ($needle, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $query = $query->orWhereRaw("UPPER($column) LIKE UPPER($needle)");
                }
            }
        );

        $models = $search->orderBy('email', 'asc')->get()->all();

        return $this->parseModelArray($models);
    }

    /**
     * Idempotently create or update a contact
     *
     * Contacts emails are unique per user. If the contact object has an id, we
     * assume it refers to an existing contact and attempt an update. If a
     * contact with that id does not exist, we silently create a new contact.
     * If there is a uniqueness conflict, we throw an exception. If there is no
     * contact id, we attempt to find a contact via its email, and if present,
     * we update that contact. If not present we create.
     *
     * We have no guard to ensure that a contact has an email present, and
     * assume that that kind of validation will be handled at a higher layer.
     *
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function sync(Contact $contact)
    {
        $existing = $this->getByEmail($contact->getEmail());
        if ($existing !== null) {
            $contact->setId($existing->getId());
            $contact->setExternalId($existing->getExternalId());

            return $this->update($contact);
        }

        return $this->create($contact);
    }

    /**
     * Create a contact
     *
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function create(Contact $contact)
    {
        $model = new ContactModel();
        $model = $this->saveModelFromContact($contact, $model);
        return $this->makeContactFromModel($model);
    }

    /**
     * Update a contact
     *
     * @param Contact $contact
     *
     * @return Contact|null
     */
    public function update(Contact $contact)
    {
        $model = ContactModel::where('id', $contact->getId())
            ->where('user_id', $this->userId)
            ->first();

        if (!$model) {
            ContactRepoException::throwNotFoundException();
        }

        $model = $this->saveModelFromContact($contact, $model);
        return $this->makeContactFromModel($model);
    }

    /**
     * Delete a contact
     *
     * @param int $contactId
     *
     * @return void
     */
    public function delete(int $contactId)
    {
        $model = ContactModel::where('id', $contactId)
            ->where('user_id', $this->userId)
            ->first();

        if ($model) {
            $model->delete();
        }
    }

    /**
     * Set the User Id to restrict repository requests by
     *
     * @param int $userId
     *
     * @return void
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the User Id to restrict repository requests by
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
