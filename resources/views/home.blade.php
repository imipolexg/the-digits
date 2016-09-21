@extends('layouts.app')

@section('content')
<div class="container main">
    <div class="row contacts-list">
        <div class="list-group col-md-12 col-xs-12" id="contacts-list-group"></div>
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
            <div class="col-xs-8 col-xs-offset-2">
            <div id="edit-modal-error" class="hidden" style="margin: 5px; padding: 5px; background-color: #F5A2A2; color: #C61414">Fly me to the danger zone</div>
            </div>
        </div>
        <div class="row">
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="edit-contact-email" class="col-xs-2 col-xs-offset-1  control-label">E-Mail</label>
                    <div class="col-xs-7 col-xs-offset-1">
                        <input id="edit-contact-email" name="edit-contact-email" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-name" class="col-xs-2 col-xs-offset-1  control-label">First Name</label>
                    <div class="col-xs-7 col-xs-offset-1">
                        <input id="edit-contact-first-name" name="edit-contact-first-name" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-name" class="col-xs-2 col-xs-offset-1 control-label">Last Name</label>
                    <div class="col-xs-7 col-xs-offset-1">
                        <input id="edit-contact-last-name" name="edit-contact-last-name" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-contact-phone" class="col-xs-2 col-xs-offset-1 control-label">Phone</label>
                    <div class="col-xs-7 col-xs-offset-1">
                        <input id="edit-contact-phone" name="edit-contact-phone" class="form-control" type="text" />
                    </div>
                </div>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="control-label col-xs-10 col-xs-offset-1">
                Custom Fields
                <span class="pull-right"><button id="add-custom-field" type="button" class="btn btn-default btn-sm glyphicon glyphicon-plus"></button></span>
            </div>
        </div>
        <br/>
        <div class="row">
            <div id="custom-fields-elem" class="form-horizontal">
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

<!-- Delete confirm modal modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="delete-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Delete Contact</h4>
      </div>
      <div class="modal-body">
        <p id="delete-modal-text"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button id="confirm-delete" type="button" class="btn btn-primary">Delete it</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let contactListElem = $('#contacts-list-group');
    let searchGroup = $('#search-group');
    let searchInput = $('#search');
    let searchButton = $('#search-button');
    let searchIcon = $('#search-icon');
    let editModal = $('#edit-modal');
    let editModalError = $('#edit-modal-error');
    let deleteModal = $('#delete-modal');
    let customFieldsElem = $('#custom-fields-elem');
    let addCustomFieldButton = $('#add-custom-field');

    let contactList, cachedContactList, modalContact, modalContactIndex,
        modalContactElem, deleteModalContact, deleteModalIndex, deleteModalElem;

    // The contacts are sorted by email so we can lookup a contact in our
    // object list quickly this way
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
                    contactListElem.append('No contacts found for that search...');
                } else {
                    contactListElem.append('No contacts found...');
                }
            }
        });
    }

    let showDeleteModal = function (index, contact, elem) {
        deleteModalIndex = index; deleteModalContact = contact; deleteModalElem = elem;
        deleteModal.modal('show');
    };

    deleteModal.on('show.bs.modal', function () {
        $('#delete-modal-text').html("Are you sure you want to delete '" + deleteModalContact.email + "'");
    });

    $('#confirm-delete').click(function () {
        deleteContact(deleteModalIndex, deleteModalContact, deleteModalElem);
        deleteModal.modal('hide');
    });

    let renderContact = function (index, contact, elem) {
        elem.empty();
//        let removeSpan = $('<span>', { style: 'cursor: pointer; color: white', class: 'glyphicon glyphicon-remove' });
        let removeSpan = $('<span>', { class: 'pull-right' });
        let removeButton = $('<button>', { class: 'btn btn-default btn-sm glyphicon glyphicon-remove' });
        removeButton.click(function (evt) {
            showDeleteModal(index, contact, elem);
//            deleteContact(index, contact, elem);
        });
        removeSpan.append(removeButton);
        elem.append(removeSpan);

        let wrapperA = $('<a>', { href: '#', class: 'contact-link', id: 'contact-link-' + index});
        let heading = $('<h4>', { class: 'list-group-item-heading' });
        heading.html(contact.email);
        wrapperA.append(heading);
        wrapperA.click(function (evt) {
            evt.preventDefault();
            modalContact = contact;
            modalContactIndex = index;
            modalContactElem = elem;
            editModal.modal('show');
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

    let doSearch = function (needle) {
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
    };

    let clearSearch = function (needle) {
        if (cachedContactList) {
            contactList = cachedContactList;
            cachedContactList = undefined;
        }
        constructContactsList(contactList);
    };

    // Clear search if backspace erases search completely
    searchInput.keydown(function (evt) {
        if (evt.which === 8 || evt.which === 46) {
            let needle = searchInput.val().trim();

            if ((!needle || needle === '' || needle.length == 1) && searchIcon.hasClass('glyphicon-remove')) {
                searchButton.click();
            }
        }
    });

    // Trigger search on enter key
    searchInput.keypress(function (evt) {

        if (evt.which !== 13) {
            return;
        }

        let needle = searchInput.val().trim();
        if (!needle) { needle = ''; }

        if (searchIcon.hasClass('glyphicon-search') || needle === '') {
            // If in search state, act like the button was clicked, if in
            // remove state and the input is blank, clear the search
            searchButton.click();
        } else {
            // If the button is in the remove state, we should just do the search
            doSearch(needle);
        }
    });

    searchButton.click(function () {
        let needle = searchInput.val().trim();
        if (!needle) { needle = ''; }

        if (searchIcon.hasClass('glyphicon-search') && needle !== '') {
            searchIcon.removeClass('glyphicon-search');
            searchIcon.addClass('glyphicon-remove');
            doSearch(needle);
        } else if (searchIcon.hasClass('glyphicon-remove')) {
            searchIcon.removeClass('glyphicon-remove');
            searchIcon.addClass('glyphicon-search');
            clearSearch();
        }
    });

    let handleEditError = function (errorObj) {
        errMsg = errorObj.responseJSON.email;
        editModalError.html(errMsg);

        if (editModalError.hasClass('hidden')) {
            editModalError.removeClass('hidden');
        }
    };

    let updateContact = function(contact) {
        return $.ajax('/contacts/' + encodeURIComponent(contact.email), {
            statusCode: { 422: handleEditError },
            method: 'PATCH',
            data: JSON.stringify(contact),
            processData: false,
            contentType: 'application/json; charset=utf-8'
        });
    };

    let createContact = function (contact) {
        return $.ajax('/contacts', {
            statusCode: { 422: handleEditError },
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

    editModal.on('show.bs.modal', function () {
        customFieldsElem.empty();
        addCustomFieldButton.removeClass('disabled');

        if (modalContact) {
            $('#edit-modal-title').html('Edit ' + modalContact.email);
            $('#edit-contact-email').val(modalContact.email);
            $('#edit-contact-first-name').val(modalContact.firstName);
            $('#edit-contact-last-name').val(modalContact.lastName);
            $('#edit-contact-phone').val(modalContact.phone);

            for (let i = 1, j = 1; i <= 5; i++) {
                let customIndex = 'custom' + i;
                if (modalContact[customIndex] !== null) {
                    addCustomField(j);
                    $('#edit-contact-custom-' + j).val(modalContact[customIndex]);
                    j++;
                }
            }

            if ($('.custom-field-group').length === 5) {
                addCustomFieldButton.addClass('disabled');
            }
        } else {
            $('#edit-modal-title').html('Add Contact');
            $('#edit-contact-email').val(null);
            $('#edit-contact-first-name').val(null);
            $('#edit-contact-last-name').val(null);
            $('#edit-contact-phone').val(null);
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
        contact.custom1 = $('#edit-contact-custom-1').val();
        contact.custom2 = $('#edit-contact-custom-2').val();
        contact.custom3 = $('#edit-contact-custom-3').val();
        contact.custom4 = $('#edit-contact-custom-4').val();
        contact.custom5 = $('#edit-contact-custom-5').val();

        return contact;
    };

    let resortList = function () {
        contactList.sort(contactCompare);
        constructContactsList(contactList);
        if (cachedContactList) { cachedContactList.sort(contactCompare); }
    };

    let hideEditModal = function () {
        if (!editModalError.hasClass('hidden')) {
            editModalError.addClass('hidden');
        }
        editModal.modal('hide');
    };

    let removeCustomField = function (index, elem) {
        // Move ids up
        let nextGroup = elem.next('.custom-field-group');
        console.log('nextGroup', nextGroup);
        while (nextGroup.attr('id') !== undefined) {
            let inputElem = nextGroup.find('input');
            let inputId = inputElem.attr('id');
            parts = inputId.split('-');
            idPart = parseInt(parts[3]) - 1;
            parts[3] = idPart.toString()

            inputElem.attr('id', parts.join('-'));
            nextGroup = nextGroup.next('.custom-field-group');
        }

        elem.remove();
        if ($('.custom-field-group').length < 5) {
            addCustomFieldButton.removeClass('disabled');
        }
    };

    let addCustomField = function (index) {
        let inputId = 'edit-contact-custom-' + index;
        let outerDiv = $('<div>', { id: 'outercustom-' + index, class: 'form-group custom-field-group' });
        let inputGroup = $('<div>', { class: 'input-group col-xs-10 col-xs-offset-1' });
        let customInput = $('<input>', { id: inputId, name: inputId, class: 'form-control', type: 'text' });
        let btnSpan = $('<span>', { class: 'input-group-btn' });
        let removeButton = $('<button>', { class: 'btn btn-default glyphicon glyphicon-minus' });

        removeButton.click(function () {
            removeCustomField(index, outerDiv);
        });

        btnSpan.append(removeButton);
        inputGroup.append(customInput);
        inputGroup.append(btnSpan);
        outerDiv.append(inputGroup);

        $('#custom-fields-elem').append(outerDiv);
    };

    addCustomFieldButton.click(function () {
        if (addCustomFieldButton.hasClass('disabled')) {
            console.log('skip');
            return false;
        }

        let customFieldElems = $('.custom-field-group');
        let newIndex = customFieldElems.length + 1;

        addCustomField(newIndex);

        if (newIndex === 5) {
            addCustomFieldButton.addClass('disabled');
        }
    });

    $('#edit-save').click(function () {
        if (modalContact) {
            // Preserved for the binary-search of the cache below
            origEmail = modalContact.email;

            // Update our contact object
            updatedContact = grabModalValues();
            updatedContact.id = modalContact.id;

            // save it on the backend
            updateContact(updatedContact).done(function (contact) {
                hideEditModal();

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
            // We're adding a new contact
            newContact = grabModalValues();
            createContact(newContact).done(function (contact) {
                console.log('done');
                hideEditModal();

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
