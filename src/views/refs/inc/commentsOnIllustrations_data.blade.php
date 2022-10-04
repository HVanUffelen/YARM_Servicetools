@if (count($comments_on_illustrations) > 0)
    <div id="commentsOnIllustrationDataList" class="adminContent">
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
            @foreach($comments_on_illustrations as $coi)
                <tr id="tblData-id_{{ $coi->ref_id }}">
                    @foreach ($colNames as $colName)
                        <td class="C-{{$colName}}">{{$coi->$colName}}</td>
                    @endforeach

                    <td>
                        <a href="{{action('RefController@edit', [
                                'ref'=>$coi->ref_id,
                                'redirect'=>true,
                                'pageUrl'=> strtolower(config('yarm.sys_name')) . '/commentsOnIllustrations',
                                'page'=>$comments_on_illustrations->currentPage(),
                            ])}}" data-placement="top" data-toggle="popover"
                                data-trigger="hover" data-content="@lang('Edit reference')">
                            <i class="fa fa-pencil text-primary"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $comments_on_illustrations->links() }}
    </div>
@else
    <div class="text-center">
        <p>@lang('No data found.')</p>
    </div>
@endif
