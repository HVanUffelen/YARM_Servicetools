@if (count($publishers) > 0)
    {!! Form::hidden('pageUrl', $publishers->currentPage()) !!}
    <div id="publisherList" class="adminContent">
        <table class="table table-bordered">
            <thead>
            <tr>
                <td style="width: 50px">@lang('Select')</td>
                @foreach ($colNames as $colName)
                    <th>{{strtoupper(__($colName))}}</th>
                @endforeach
                <td style="width: 50px">@lang('Action')</td>
            </tr>
            </thead>
            <tbody>
            @foreach($publishers as $p)
                <tr id="tblData-id_{{ $p->ref_id }}">
                    <td>
                        {!! Form::checkbox('select[]', $p->ref_id, false, ['class' => 'form-check-input mx-0 publisherCheckbox', 'checked' => false]) !!}
                    </td>
                    @foreach ($colNames as $colName)
                        <td class="C-{{$colName}}">{{$p->$colName}}</td>
                    @endforeach

                    <td class="text-center">
                        <a href="{{action('RefController@edit', [
                                'ref'=>$p->ref_id,
                                'redirect'=>true,
                                'pageUrl'=>'dlbt/publishers',
                                'page'=>$publishers->currentPage(),
                            ])}}" data-placement="top" data-toggle="popover"
                                data-trigger="hover" data-content="@lang('Edit reference')">
                            <i class="fa fa-pencil text-primary"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="my-3 d-flex flex-row">
            {{-- // TODO Lang --}}
            {!! Form::label('edit', __('Change publishers into:'), ['class' => 'sr-only font-weight-bold']); !!}</h5>
            {!! Form::text('edit', '', ['id' => 'editPublisher', 'class' => 'form-control', 'placeholder' => __('Correct publisher name'), 'disabled' => true]) !!}
            <button type="submit" class="btn btn-success ml-3" id="confirmPublisher" disabled="true">
                @lang('Submit')
            </button>
        </div>

        {{ $publishers->links() }}

    </div>
@else
    <div class="text-center">
        <p>@lang('No data found.')</p>
    </div>
@endif
