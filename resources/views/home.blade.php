@extends('layouts.app')

@section('content')
<div class="container main">
    <div class="panel panel-default contact-panel">
        <div class="panel-heading">
            <h4 class="pull-left">Contacts for {{ Auth::user()->name }}</h4>
            <span class="pull-right">
                <button class="btn btn-default" data-toggle="modal" data-target="#edit-modal" aria-label="Add Contact"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
            </span>
            <div class="clearfix"></div>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="input-group col-md-10 col-xs-10 col-md-offset-1 col-xs-offset-1">
                    <input id="search" class="search-query form-control" type="text" name="search" placeholder="Search contacts..." />
                </div>
            </div>

            <hr/>

            <div class="contacts-list">
                <div class="list-group" id="contacts-list-group">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- modal panel -->
<div id="edit-modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="edit-cancel close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 id="edit-modal-title" class="modal-title">Add Contact</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="edit-contact-email" class="col-xs-3 col-md-3 control-label">E-Mail</label>
                    <div class="col-xs-8 col-md-8">
                        <input id="edit-contact-email" name="edit-contact-email" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-name" class="col-xs-3 col-md-3 control-label">First Name</label>
                    <div class="col-md-8 col-xs-8">
                        <input id="edit-contact-first-name" name="edit-contact-first-name" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-name" class="col-xs-3 col-md-3 control-label">Last Name</label>
                    <div class="col-md-8 col-xs-8">
                        <input id="edit-contact-last-name" name="edit-contact-last-name" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-phone" class="col-xs-3 col-md-3 control-label">Phone</label>
                    <div class="col-xs-8 col-md-8">
                        <input id="edit-contact-phone" name="edit-contact-phone" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-custom1" class="col-xs-3 col-md-3 control-label">Custom 1</label>
                    <div class="col-xs-8 col-md-8">
                        <input id="edit-contact-custom1" name="edit-contact-custom1" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-custom2" class="col-xs-3 col-md-3 control-label">Custom 2</label>
                    <div class="col-xs-8 col-md-8">
                        <input id="edit-contact-custom2" name="edit-contact-custom2" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-custom3" class="col-xs-3 col-md-3 control-label">Custom 3</label>
                    <div class="col-xs-8 col-md-8">
                        <input id="edit-contact-custom3" name="edit-contact-custom3" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-custom1" class="col-xs-3 col-md-3 control-label">Custom 1</label>
                    <div class="col-xs-8 col-md-8">
                        <input id="edit-contact-custom1" name="edit-contact-custom1" class="form-control" type="text" />
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default edit-cancel" data-dismiss="modal">Cancel</button>
        <button id="edit-save" type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let contactListElem = $('#contacts-list-group');
    let contactList, cachedContactList, modalContact, modalContactIndex, modalContactElem;

    let binarySearchContacts = function (contacts, email) {
        let max = contacts.length - 1, min = 0;

        while (max >= min) {
            let mid = min + Math.floor((max-min)/2);

            console.log(mid, contacts[mid].email);

            if (email > contacts[mid].email) {
                min = mid + 1;
            } else if (email < contacts[mid].email) {
                max = mid - 1;
            } else {
                return mid;
            }
        }

        return -1;
    };

    let deleteContact = function (index, contact, elem) {
        $.ajax('/contacts/' + encodeURIComponent(contact.email), { method: 'DELETE' }).done(function () {
            elem.remove();
            contactList[index].deleted = true;

            if (cachedContactList) {
                i = binarySearchContacts(cachedContactList, contactList[index].email);
                if (i > -1) { cachedContactList[i].deleted = true; }
            }

            if (contactListElem.children().length === 0) {
                if ($('#search').val()) {
                    contactListElem.append("No contacts found for that search...");
                } else {
                    contactListElem.append("No contacts found...");
                }
            }
        });
    }

    let renderContact = function (index, contact, elem) {
        let removeSpan = $('<span>', { style: 'cursor: pointer; color: white', class: 'glyphicon glyphicon-remove' });
        let removeBadge = $('<span>', { class: 'badge' });
        removeBadge.append(removeSpan);
        removeBadge.click(function (evt) {
            deleteContact(index, contact, elem);
        });
        elem.append(removeBadge);

        let wrapperA = $('<a>', { href: '#', class: 'contact-link', id: 'contact-link-' + index});
        let heading = $('<h4>', { class: 'list-group-item-heading' });
        heading.html(contact.email);
        wrapperA.append(heading);
        wrapperA.click(function (evt) {
            evt.preventDefault();
            modalContact = contact;
            modalContactIndex = index;
            modalContactElem = elem;
            $('#edit-modal').modal('show');
        });

        let itemText = $('<p>', { class: 'list-group-item-text' });
        let itemTextHtml = '';
        if (contact.firstName && contact.lastName) {
            itemTextHtml = contact.lastName + ', ' + contact.firstName + '<br/>';
        } else if (contact.firstName) {
            itemTextHtml = contact.firstName + '<br/>';
        } else if (contact.lastName) {
            itemTextHtml = contact.lastName + '<br/>';
        }

        for (prop of ['phone', 'custom1', 'custom2', 'custom3', 'custom4', 'custom5']) {
            if (contact[prop]) {
                itemTextHtml += contact[prop] + '<br/>';
            }
        }

        itemText.html(itemTextHtml)
        wrapperA.append(itemText);
        elem.empty();
        elem.append(wrapperA);
    };

    let addContactToList = function(index, contact) {
        let li = $('<li>', { class: "contact-item list-group-item", id: 'contact-' + index });
        renderContact(index, contact, li);
        contactListElem.append(li);
    };

    let constructContactsList = function (contacts) {
        contactListElem.empty();

        let didAddAContact = false;
        for (let i = 0; i < contacts.length; i++) {
            if (contacts[i].deleted) {
                continue;
            }
            addContactToList(i, contacts[i]);
            didAddAContact = true;
        }

        if (!didAddAContact) {
            contactListElem.append("No contacts found...");
            return;
        }
    };

    let loadContacts = function () {
        $.ajax('/contacts').done(function (contacts) {
            contactList = contacts;
            constructContactsList(contactList);
        });
    };

    // Trigger search on enter key
    $('#search').keyup(function () {
        let needle = $('#search').val().trim();
        console.log('needle = ' + needle);

        if (needle === '' && cachedContactList) {
            contactList = cachedContactList;
            cachedContactList = undefined;
            constructContactsList(contactList);
            return;
        }

        contactListElem.empty();
        contactListElem.append("Searching for '" + needle + "'");

        needle = encodeURIComponent(needle);
        $.ajax('/contacts?search=' + needle).done(function (contacts) {
            if (!cachedContactList) {
                cachedContactList = contactList;
            }
            contactList = contacts;
            constructContactsList(contactList);
        });
    });

    let updateContact = function(contact) {
        return $.ajax('/contacts/' + encodeURIComponent(contact.email), {
            method: 'PATCH',
            data: JSON.stringify(contact),
            processData: false,
            contentType: 'application/json; charset=utf-8'
        });
    };

    let createContact = function (contact) {
        return $.ajax('/contacts', {
            method: 'POST',
            data: JSON.stringify(contact),
            processData: false,
            contentType: 'application/json; charset=utf-8'
        });
    };

    let contactCompare = function (a, b) {
        if (a.email > b.email) {
            return 1;
        } else if (a.email < b.email) {
            return -1;
        }

        return 0;
    };

    $('#edit-modal').on('show.bs.modal', function () {
        if (modalContact) {
            $('#edit-modal-title').html('Edit ' + modalContact.email);
            $('#edit-contact-email').val(modalContact.email);
            $('#edit-contact-first-name').val(modalContact.firstName);
            $('#edit-contact-last-name').val(modalContact.lastName);
            $('#edit-contact-phone').val(modalContact.phone);
            $('#edit-contact-custom1').val(modalContact.custom1);
            $('#edit-contact-custom2').val(modalContact.custom2);
            $('#edit-contact-custom3').val(modalContact.custom3);
            $('#edit-contact-custom4').val(modalContact.custom4);
            $('#edit-contact-custom5').val(modalContact.custom5);
        } else {
            $('#edit-modal-title').html('Add Contact');
            $('#edit-contact-email').val(null);
            $('#edit-contact-first-name').val(null);
            $('#edit-contact-last-name').val(null);
            $('#edit-contact-phone').val(null);
            $('#edit-contact-custom1').val(null);
            $('#edit-contact-custom2').val(null);
            $('#edit-contact-custom3').val(null);
            $('#edit-contact-custom4').val(null);
            $('#edit-contact-custom5').val(null);
        }
    });

    $('.edit-cancel').click(function () {
        if (modalContact) {
            modalContact = undefined;
        }
    });

    let grabModalValues = function () {
        contact = {}
        contact.email = $('#edit-contact-email').val();
        contact.firstName= $('#edit-contact-first-name').val();
        contact.lastName= $('#edit-contact-last-name').val();
        contact.phone = $('#edit-contact-phone').val();
        contact.custom1 = $('#edit-contact-custom1').val();
        contact.custom2 = $('#edit-contact-custom2').val();
        contact.custom3 = $('#edit-contact-custom3').val();
        contact.custom4 = $('#edit-contact-custom4').val();
        contact.custom5 = $('#edit-contact-custom5').val();

        return contact;
    };

    let resortList = function () {
        contactList.sort(contactCompare);
        constructContactsList(contactList);
        if (cachedContactList) { cachedContactList.sort(contactCompare); }
    };

    $('#edit-save').click(function () {
        if (modalContact) {
            // Preserved for the binary-search of the cache below
            origEmail = modalContact.email;

            // Update our contact object
            updatedContact = grabModalValues();
            updatedContact.id = modalContact.id;

            // save it on the backend
            updateContact(updatedContact).done(function (contact) {
                $('#edit-modal').modal('hide');
                modalContact = undefined;

                contactList[modalContactIndex] = contact;

                // If we have a cached contact list for quick rendering, update the contact in it
                if (cachedContactList) {
                    i = binarySearchContacts(cachedContactList, origEmail);

                    if (i > -1) {
                        cachedContactList[i] = contact;
                        cachedContactList.sort(contactCompare);
                    }
                }

                resortList();
            });
        } else {
            newContact = grabModalValues();
            createContact(newContact).done(function (contact) {
                $('#edit-modal').modal('hide');
                modalContact = undefined;

                contactList.push(contact);
                if (cachedContactList) {
                    cachedContactList.push(contact);
                }

                resortList();
            });
        }
    });

    // Load all contacts on boot
    loadContacts();
});
</script>
@endsection
