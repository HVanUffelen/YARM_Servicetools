@if (count($comments_on_translation) > 0)
    <div id="commentsOnTranslationDataList" class="adminContent">
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
            @foreach($comments_on_translation as $cot)
                <tr id="tblData-id_{{ $cot->ref_id }}">
                    @foreach ($colNames as $colName)
                        <td class="C-{{$colName}}">{{$cot->$colName}}</td>
                    @endforeach

                    <td>
                        <a href="{{action('RefController@edit', [
                                'ref'=>$cot->ref_id,
                                'redirect'=>true,
                                'pageUrl'=> strtolower(config('yarm.sys_name')) . '/commentsOnTranslation',
                                'page'=>$comments_on_translation->currentPage(),
                            ])}}" data-placement="top" data-toggle="popover"
                                data-trigger="hover" data-content="@lang('Edit reference')">
                            <i class="fa fa-pencil text-primary"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $comments_on_translation->links() }}
    </div>
@else
    <div class="text-center">
        <p>@lang('No data found.')</p>
    </div>
@endif
