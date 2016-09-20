<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contact;
use App\ContactRepositoryInterface;
use App\ContactRepoException;
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

        return $this->exceptionWrapper($request, null, function ($request, $email) {
            $contacts = $this->repo->getAll();
            return response()->json($contacts);
        });
    }

    public function create(Request $request)
    {
        $this->repo->setUserId($request->user()->id);
        $this->validate($request, $this->validations);

        return $this->exceptionWrapper($request, null, function ($request, $email) {
            $contact = $this->repo->create($this->makeContactFromArray($request->all()));
            return response()->json($contact);
        });
    }

    public function find(Request $request, $email)
    {
        $this->repo->setUserId($request->user()->id);

        return $this->exceptionWrapper($request, $email, function ($request, $email) {
            $contact = $this->repo->getByEmail($email);
            return response()->json($contact);
        });
    }

    public function update(Request $request, $email)
    {
        $this->repo->setUserId($request->user()->id);
        $this->validate($request, $this->validations);

        return $this->exceptionWrapper($request, $email, function ($request, $email) {
            $contact = $this->makeContactFromArray($request->all());
            $contact = $this->repo->sync($contact);

            return response()->json($contact);
        });
    }

    public function delete(Request $request, $email)
    {
        $this->repo->setUserId($request->user()->id);

        return $this->exceptionWrapper($request, $email, function ($request, $email) {
            $contact = $this->repo->getByEmail($email);
            $this->repo->delete($contact->getId());

            return response('No Content', 204);
        });
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


    /**
     * We wrap up the route methods here to simplify error handling
     */
    protected function exceptionWrapper($request, $email, $callback)
    {
        try {
            return $callback($request, $email);
        } catch (ContactRepoException $ex) {
            switch ($ex->getCode()) {
            case ContactRepoException::DUPLICATE_EMAIL:
                return response()->json(['email' => $ex->getMessage()], 400);
            case ContactRepoException::CONTACT_NOT_FOUND:
            case ContactRepoException::CONTACT_NOT_FOUND_EMAIL:
                return response($ex->getMessage(), 404);
            default:
                return response($ex->getMessage(), 500);
            }
        }
    }
}
