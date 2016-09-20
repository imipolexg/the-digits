<?php namespace App;

use Exception;

class ContactRepoException extends Exception
{

    const DUPLICATE_EMAIL         = 1;
    const CONTACT_NOT_FOUND       = 1 << 2;
    const CONTACT_NOT_FOUND_EMAIL = 1 << 3;

    public static function throwDuplicateException()
    {
        throw new ContactRepoException(trans('contactrepo_errs.duplicate'), ContactRepoException::DUPLICATE_EMAIL);
    }

    public static function throwNotFoundException()
    {
        throw new ContactRepoException(trans('contactrepo_errs.notfound'), ContactRepoException::CONTACT_NOT_FOUND);
    }

    public static function throwNotFoundWithEmailException()
    {
        throw new ContactRepoException(trans('contactrepo_errs.notfound_email'), ContactRepoException::CONTACT_NOT_FOUND_EMAIL);
    }
}
