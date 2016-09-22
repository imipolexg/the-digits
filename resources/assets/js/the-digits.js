$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var contactListElem = $('#contacts-list-group');
    var searchGroup = $('#search-group');
    var searchInput = $('#search');
    var searchButton = $('#search-button');
    var searchIcon = $('#search-icon');
    var editModal = $('#edit-modal');
    var editModalError = $('#edit-modal-error');
    var deleteModal = $('#delete-modal');
    var customFieldsElem = $('#custom-fields-elem');
    var addCustomFieldButton = $('#add-custom-field');

    var contactList, cachedContactList, modalContact, modalContactIndex,
        modalContactElem, deleteModalContact, deleteModalIndex, deleteModalElem;

    // The contacts are sorted by email so we can lookup a contact in our
    // object list quickly this way
    var binarySearchContacts = function (contacts, email) {
        var max = contacts.length - 1, min = 0;

        while (max >= min) {
            var mid = min + Math.floor((max-min)/2);

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

    var deleteContact = function (index, contact, elem) {
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

    var showDeleteModal = function (index, contact, elem) {
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

    var renderContact = function (index, contact, elem) {
        elem.empty();
//        var removeSpan = $('<span>', { style: 'cursor: pointer; color: white', class: 'glyphicon glyphicon-remove' });
        var removeSpan = $('<span>', { class: 'pull-right' });
        var removeButton = $('<button>', { class: 'btn btn-default btn-sm glyphicon glyphicon-remove' });
        removeButton.click(function (evt) {
            showDeleteModal(index, contact, elem);
//            deleteContact(index, contact, elem);
        });
        removeSpan.append(removeButton);
        elem.append(removeSpan);

        var wrapperA = $('<a>', { href: '#', class: 'contact-link', id: 'contact-link-' + index});
        var heading = $('<h4>', { class: 'list-group-item-heading' });
        heading.html(contact.email);
        wrapperA.append(heading);
        wrapperA.click(function (evt) {
            evt.preventDefault();
            modalContact = contact;
            modalContactIndex = index;
            modalContactElem = elem;
            editModal.modal('show');
        });

        var itemText = $('<p>', { class: 'list-group-item-text' });
        var itemTextHtml = '';
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

    var addContactToList = function(index, contact) {
        var li = $('<li>', { class: "contact-item list-group-item", id: 'contact-' + index });
        renderContact(index, contact, li);
        contactListElem.append(li);
    };

    var constructContactsList = function (contacts) {
        contactListElem.empty();

        var didAddAContact = false;
        for (var i = 0; i < contacts.length; i++) {
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

    var loadContacts = function () {
        $.ajax('/contacts').done(function (contacts) {
            contactList = contacts;
            constructContactsList(contactList);
        });
    };

    var doSearch = function (needle) {
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

    var clearSearch = function (needle) {
        if (cachedContactList) {
            contactList = cachedContactList;
            cachedContactList = undefined;
        }
        constructContactsList(contactList);
    };

    // Clear search if backspace erases search completely
    searchInput.keydown(function (evt) {
        if (evt.which === 8 || evt.which === 46) {
            var needle = searchInput.val().trim();

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

        var needle = searchInput.val().trim();
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
        var needle = searchInput.val().trim();
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

    var handleEditError = function (errorObj) {
        errMsg = errorObj.responseJSON.email;
        editModalError.html(errMsg);

        if (editModalError.hasClass('hidden')) {
            editModalError.removeClass('hidden');
        }
    };

    var updateContact = function(contact) {
        return $.ajax('/contacts/' + encodeURIComponent(contact.email), {
            statusCode: { 422: handleEditError },
            method: 'PATCH',
            data: JSON.stringify(contact),
            processData: false,
            contentType: 'application/json; charset=utf-8'
        });
    };

    var createContact = function (contact) {
        return $.ajax('/contacts', {
            statusCode: { 422: handleEditError },
            method: 'POST',
            data: JSON.stringify(contact),
            processData: false,
            contentType: 'application/json; charset=utf-8'
        });
    };

    var contactCompare = function (a, b) {
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

            for (var i = 1, j = 1; i <= 5; i++) {
                var customIndex = 'custom' + i;
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

    var grabModalValues = function () {
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

    var resortList = function () {
        contactList.sort(contactCompare);
        constructContactsList(contactList);
        if (cachedContactList) { cachedContactList.sort(contactCompare); }
    };

    var hideEditModal = function () {
        if (!editModalError.hasClass('hidden')) {
            editModalError.addClass('hidden');
        }
        editModal.modal('hide');
    };

    var removeCustomField = function (index, elem) {
        // Move ids up
        var nextGroup = elem.next('.custom-field-group');
        console.log('nextGroup', nextGroup);
        while (nextGroup.attr('id') !== undefined) {
            var inputElem = nextGroup.find('input');
            var inputId = inputElem.attr('id');
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

    var addCustomField = function (index) {
        var inputId = 'edit-contact-custom-' + index;
        var outerDiv = $('<div>', { id: 'outercustom-' + index, class: 'form-group custom-field-group' });
        var inputGroup = $('<div>', { class: 'input-group col-xs-10 col-xs-offset-1' });
        var customInput = $('<input>', { id: inputId, name: inputId, class: 'form-control', type: 'text' });
        var btnSpan = $('<span>', { class: 'input-group-btn' });
        var removeButton = $('<button>', { class: 'btn btn-default glyphicon glyphicon-minus' });

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

        var customFieldElems = $('.custom-field-group');
        var newIndex = customFieldElems.length + 1;

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

