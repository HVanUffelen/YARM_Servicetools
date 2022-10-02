@extends('resources.views.layouts.app')
{{App()->setLocale(Session::get('userLanguage'))}}

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>@lang('Comments on illustrations')
            </h3>
        </div>
        <div class="card-body pb-0">
            {!! Form::label('search', __('Show only results containing:'), ['class' => 'sr-only font-weight-bold']); !!}</h5>
            {!! Form::text('search', '', ['class' => 'form-control', 'placeholder' => __('Search by name')]) !!}
        </div>
        <div class="card-body">
            @include('servicetools::refs.inc.commentsOnIllustrations_data')
        </div>
    </div>
    <input type="hidden" name="type" id="type" value="{{'commentsOnIllustrations'}}"/>
    <input type="hidden" name="view" id="view" value="{{'commentsOnIllustrations'}}"/>
@endsection
