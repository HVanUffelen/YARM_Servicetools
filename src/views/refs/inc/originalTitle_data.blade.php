@if (count($original_titles) > 0)
    <div id="originalTitleDataList" class="adminContent">
        <table class="table table-bordered">
            <thead>
            <tr>
                @foreach ($colNames as $colName)
                    <th>{{strtoupper(__($colName))}}</th>
                @endforeach
                <td colspan="2">@lang('Action')</td>
            </tr>
            </thead>
            <tbody>
            @foreach($original_titles as $ot)
                <tr id="tblData-id_{{ $ot->ref_id }}">
                    @foreach ($colNames as $colName)
                        <td class="C-{{$colName}}">{{$ot->$colName}}</td>
                    @endforeach

                    <td>
                        <a href="{{action('RefController@edit', [
                                'ref'=>$ot->ref_id,
                                'redirect'=>true,
                                'pageUrl'=>'dlbt/originalTitles',
                                'page'=>$original_titles->currentPage(),
                            ])}}" data-placement="top" data-toggle="popover"
                                data-trigger="hover" data-content="@lang('Edit reference')">
                            <i class="fa fa-pencil text-primary"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $original_titles->links() }}
    </div>
@else
    <div class="text-center">
        <p>@lang('No data found.')</p>
    </div>
@endif
