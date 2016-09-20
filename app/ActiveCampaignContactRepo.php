<?php namespace App;

use App\ContactRepositoryInterface;
use App\ContactRepoException;
use App\Contact;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

/**
 * Partial implementation of the ActiveCampaign API
 *
 * http://www.activecampaign.com/api/overview.php
 *
 * Only implements the contact_create, _edit, _sync, _delete, and _view
 * actions
 *
 * Interestingly, the ActiveCampaign API supports prudocing serialized PHP
 * objects for us to consume, which should be marginally faster to parse than
 * JSON or XML, so that's what we'll use.
 *
 * Performs the network calls synchronously, so you might want to place API
 * calls in an async job queue when possible.
 *
 */
class ActiveCampaignContactRepo implements ExternalContactRepositoryInterface
{
    protected static $API_PATH = '/admin/api.php';

    protected $apiKey;
    protected $client;
    protected $endpoint;

    public function __construct()
    {
        $this->endpoint = rtrim(config('active_campaign')['endpoint'], '/');
        $this->apiKey = config('active_campaign')['api_key'];
        $this->client = new GuzzleClient([
            'base_uri' => $this->endpoint . self::$API_PATH
        ]);
    }

    public function get(int $externalId)
    {
        $params = [ 'id' => $externalId ];
        $resp = $this->makeRequest('contact_view', $params);

        return $this->parseContactArray($resp);
    }

    public function getByEmail(string $email)
    {
        $params = ['email' => $email];
        $resp = $this->makeRequest('contact_view_email', $params);

        return $this->parseContactArray($resp);
    }

    public function getAll()
    {
        throw ContactRepoException('Not implemented');
    }

    public function sync(Contact $contact)
    {
        $resp = $this->makeRequest('contact_sync', [], $contact);
        $contact->setExternalId($resp['subscriber_id']);

        return $contact;
    }

    public function create(Contact $contact)
    {
        $resp = $this->makeRequest('contact_add', [], $contact);
        $contact->setExternalId($resp['subscriber_id']);

        return $contact;
    }

    public function update(Contact $contact)
    {
        $params = [ 'overwrite' => 0 ];
        $resp = $this->makeRequest('contact_edit', $params, $contact);

        return $contact;
    }

    public function delete(int $externalId)
    {
        $params = [ 'id' => $externalId ];
        $this->makeRequest('contact_delete', $params);
    }

    protected function makeQueryParams(string $action)
    {
        return [
            'api_key'    => $this->apiKey,
            'api_output' => 'serialize',
            'api_action' => $action
        ];
    }

    protected function makeRequest(string $action, array $extraParams = [], Contact $contact = null)
    {
        $actionMethods = [
            'contact_add'        => 'POST',
            'contact_delete'     => 'GET',
            'contact_edit'       => 'POST',
            'contact_sync'       => 'POST',
            'contact_view'       => 'GET',
            'contact_view_email' => 'GET'
        ];

        $queryParams = array_merge($this->makeQueryParams($action), $extraParams);
        $options = [
            'query'       => $queryParams,
            'http_errors' => true
        ];

        if ($contact !== null) {
            $formData = $this->makeContactArray($contact);
            $options['form_params'] = $formData;
        }

        try {
            $res = $this->client->request($actionMethods[$action], '', $options);
        } catch (RequestException $ex) {
            throw new ContactRepoException($ex->getMessage());
        }

        $resObj = unserialize($res->getBody());
        if ($resObj['result_code'] == 0) {
            throw new ContactRepoException($resObj['result_message']);
        }

        return $resObj;
    }

    protected function parseContactArray(array $contactArray)
    {
        $contact = new Contact();
        $contact->setEmail($contactArray['email']);
        $contact->setExternalId($contactArray['id']);
        $contact->setFirstName($contactArray['first_name']);
        $contact->setLastName($contactArray['last_name']);
        $contact->setPhone($contactArray['phone']);

        return $contact;
    }

    protected function makeContactArray(Contact $contact)
    {
        $contactArray = [
            'email' => $contact->getEmail(),
            'first_name' => $contact->getFirstName(),
            'last_name' => $contact->getLastName(),
            'phone' => $contact->getPhone()
        ];

        if ($contact->getExternalId() !== null) {
            $contactArray['id'] = $contact->getExternalId();
        }

        return $contactArray;
    }
}
