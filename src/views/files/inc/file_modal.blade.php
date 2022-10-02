<form id="editFileForm" name="editFileForm" class="form-horizontal">
    <div class="modal fade" id="modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        @lang('Edit file name')
                    </h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '', ['class' => 'id']) !!}
                    <div class="form-group">
                        {!! Form::label('name', __('Name') . ' (*)'); !!}
                        {!! Form::text('name', '', ['class' => 'form-control name', 'placeholder' => __('Name'), 'required' => true] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('local_name', __('Local name') . ' (*)'); !!}
                        {!! Form::text('local_name', '', ['class' => 'form-control local_name', 'placeholder' => __('Local name'), 'required' => true] ) !!}
                    </div>
                    {{-- // TODO Lang GER \\ --}}
                    <p class="text-danger mt-2">@lang('Please make sure these two fields are the same')!</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btn-file-update">
                        @lang('Submit')
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
