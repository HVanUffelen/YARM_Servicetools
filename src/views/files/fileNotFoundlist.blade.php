@extends('layouts.app')
{{App()->setLocale(Session::get('userLanguage'))}}

 @section('content')
    @include('servicetools::files.inc.file_modal')
    <div class="card">
        <div class="card-header">
            <h3>@lang('Unfound files')
                <a title="Info" data-placement="top" data-toggle="popover"
                    data-trigger="hover"
                    {{-- // TODO Lang GER \\ --}}
                    data-content="@lang('This is a list with files that could not be found in the local storage, most likely because it has special characters. Please make sure these files have the same name and local_name')."><i
                        class="fa fa-info-circle"
                        style="color: grey"></i></a>
            </h3>
        </div>
        <div class="card-body pb-0">
            {!! Form::label('search', __('Show only results containing:'), ['class' => 'sr-only font-weight-bold']); !!}</h5>
            {!! Form::text('search', '', ['class' => 'form-control', 'placeholder' => __('Search by name')]) !!}
        </div>
        <div class="card-body">
            @include('servicetools::files.inc.fileNotFound_data')
        </div>
    </div>
    <a class="btn btn-primary text-white float-right mt-3" href="{{ route('change_file_names') }}">
        @lang('Clean file names')
    </a>
    <input type="hidden" name="type" id="type" value="{{'filesNotFound'}}"/>
    <input type="hidden" name="view" id="view" value="{{'filesNotFound'}}"/>
@endsection
