@extends('layouts.app')
{{App()->setLocale(Session::get('userLanguage'))}}

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>@lang('Publishers')
                <a title="Info" data-placement="top" data-toggle="popover"
                    data-trigger="hover"
                    data-content="@lang('Select the publisher(s) whose names are incorrect and fill in the correct name below.')"><i
                        class="fa fa-info-circle"
                        style="color: grey"></i></a>
            </h3>
        </div>
        <div class="card-body pb-0">
            {!! Form::label('search', __('Show only results containing:'), ['class' => 'sr-only font-weight-bold']); !!}</h5>
            {!! Form::text('search', '', ['class' => 'form-control', 'placeholder' => __('Search by name')]) !!}
        </div>
        <div class="card-body">
            {!! Form::open(['id' => 'publisherConfirm', 'action' => 'DataCleaningController@confirmPublisher', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
            @include('servicetools::refs.inc.publisher_data')
            {!! Form::close() !!}
        </div>
    </div>
    <input type="hidden" name="type" id="type" value="{{'publishers'}}"/>
    <input type="hidden" name="view" id="view" value="{{'publishers'}}"/>
@endsection
