<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contact;
use App\ContactRepositoryInterface;
use App\ContactRepoException;
use App\Events\ContactChangeEvent;
use App\Events\ContactDeleteEvent;
use Auth;

class ContactsController extends Controller
{

    protected $repo;

    protected $validations = [
        'email' => 'required|email|max:255',
    ];

    /**
     * Create a new controller
     *
     * @return void
     */
    public function __construct(ContactRepositoryInterface $repo)
    {
        $this->middleware('auth');
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $this->repo->setUserId($request->user()->id);

        if ($request->input('search')) {
            $needle = $request->input('search');
            $contacts = $this->repo->search($needle);
        } else {
            $contacts = $this->repo->getAll();
        }

        return response()->json($contacts);
    }

    public function create(Request $request)
    {
        $this->repo->setUserId($request->user()->id);
        $this->validate($request, $this->validations);

        $contact = $this->repo->create($this->makeContactFromArray($request->all()));

        event(new ContactChangeEvent($contact, $this->repo->getUserId()));

        return response()->json($contact);
    }

    public function find(Request $request, $email)
    {
        $this->repo->setUserId($request->user()->id);

        $contact = $this->repo->getByEmail($email);

        return response()->json($contact);
    }

    public function update(Request $request, $email)
    {
        $this->repo->setUserId($request->user()->id);
        $this->validate($request, $this->validations);

        $contact = $this->makeContactFromArray($request->all());

        // We use sync because it will not overwrite an existing externalId
        $contact = $this->repo->sync($contact);

        event(new ContactChangeEvent($contact, $this->repo->getUserId()));

        return response()->json($contact);
    }

    public function delete(Request $request, $email)
    {
        $this->repo->setUserId($request->user()->id);

        $contact = $this->repo->getByEmail($email);
        $this->repo->delete($contact->getId());

        event(new ContactDeleteEvent($contact));

        return response('No Content', 204);
    }

    protected function makeContactFromArray(array $contactArray)
    {
        $contact = new Contact();

        foreach ($contactArray as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (!method_exists($contact, $setter)) {
                continue;
            }

            $contact->{$setter}($value);
        }

        return $contact;
    }
}
