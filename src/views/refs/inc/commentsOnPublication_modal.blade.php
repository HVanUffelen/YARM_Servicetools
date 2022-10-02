{{-- // TODO Lang --}}
<form id="commentsOnPublicationForm" name="commentsOnPublicationForm" class="form-horizontal">
    <div class="modal fade" id="modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="typeModal">
                        @lang('Edit comments on publication')
                    </h4>
                </div>
                <div class="modal-body">
                    {!! Form::hidden('id', '', ['id' => 'id',]) !!}
                    <div class="form-group">
                        {!! Form::label('title', __('Title') . ' (*)'); !!}
                        {!! Form::text('title', '', ['id' => 'title', 'class' => 'form-control', 'placeholder' => __('Title'), 'required'=>true] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('subtitle', __('Subtitle') . ' (*)'); !!}
                        {!! Form::text('subtitle', '', ['id' => 'subtitle', 'class' => 'form-control', 'placeholder' => __('Subtitle')] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('comments_on_publication', __('Comments on publication') . ' (*)'); !!}
                        {!! Form::text('comments_on_publication', '', ['id' => 'comments_on_publication', 'class' => 'form-control', 'placeholder' => __('Comments on publication')] ) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary" id="btn-coPublication-save"
                            value="create-tblrow">@lang('Submit')
                </div>
            </div>
        </div>
    </div>
</form>
