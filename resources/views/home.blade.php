@extends('layouts.app')

@section('content')
<div class="container main">
    <div class="panel panel-default contact-panel">
        <div class="panel-heading">
            <h4 class="pull-left">Contacts for {{ Auth::user()->name }}</h4>
            <span class="pull-right">
                <button class="btn btn-default" data-toggle="modal" data-target="#add-modal" aria-label="Add Contact"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
            </span>
            <div class="clearfix"></div>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="input-group col-md-10 col-xs-10 col-md-offset-1 col-xs-offset-1">
                    <input id="search" class="search-query form-control" type="text" name="search" placeholder="Search contacts..." />
                    <span class="input-group-btn">
                        <button id="search-button" class="btn btn-default" aria-label="Search"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                    </span>
                </div>
            </div>

            <hr/>

            <div class="contacts-list">
                <div class="list-group" id="contacts-list-group">
                    <li id="contact-1" class="contact-item list-group-item">
                        <span class="badge"><span style="color: white" class="glyphicon glyphicon-remove"></span></span><a href="#">
                        <h4 class="list-group-item-heading">hamm.zachary@gmail.com</h4><p class="list-group-item-text">
Hamm, Zachary<br/>
555-555-5555<br/>
Custom1<br/>
Custom2<br/>
Custom3<br/>
Custom4<br/>
Custom5<br/>
</p>
</a>
                    </li>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="add-modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add Contact</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="add-contact-name" class="col-md-3 control-label">Name</label>
                    <div class="col-md-8">
                        <input id="add-contact-name" name="add-contact-name" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-contact-telephone" class="col-md-3 control-label">Telephone</label>
                    <div class="col-md-8">
                        <input id="add-contact-telephone" name="add-contact-telephone" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-contact-email" class="col-md-3 control-label">E-Mail</label>
                    <div class="col-md-8">
                        <input id="add-contact-email" name="add-contact-email" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-contact-custom1" class="col-md-3 control-label">Custom 1</label>
                    <div class="col-md-8">
                        <input id="add-contact-custom1" name="add-contact-custom1" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-contact-custom2" class="col-md-3 control-label">Custom 2</label>
                    <div class="col-md-8">
                        <input id="add-contact-custom2" name="add-contact-custom2" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-contact-custom3" class="col-md-3 control-label">Custom 3</label>
                    <div class="col-md-8">
                        <input id="add-contact-custom3" name="add-contact-custom3" class="form-control" type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="add-contact-custom1" class="col-md-3 control-label">Custom 1</label>
                    <div class="col-md-8">
                        <input id="add-contact-custom1" name="add-contact-custom1" class="form-control" type="text" />
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
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

    let contactListElem = $('#contact-list-group');

    let addContactToList = function(contact) {

    };

    var contactList;

    $.ajax('/contacts').done(function (contacts) {
        contactList = contacts;
    });

    $('#add-contact').click(function () {
        $.ajax('/contacts').done(function (data) { console.log(data); } );
    });
});
</script>
@endsection
