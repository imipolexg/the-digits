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
<script src="/js/the-digits.js"></script>
@endsection
