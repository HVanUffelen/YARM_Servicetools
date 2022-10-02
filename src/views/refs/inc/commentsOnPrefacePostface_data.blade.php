@if (count($comments_on_preface_postface) > 0)
    <div id="commentsOnPrefacePostfaceDataList" class="adminContent">
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
            @foreach($comments_on_preface_postface as $copp)
                <tr id="tblData-id_{{ $copp->ref_id }}">
                    @foreach ($colNames as $colName)
                        <td class="C-{{$colName}}">{{$copp->$colName}}</td>
                    @endforeach

                    <td>
                        <a href="{{action('RefController@edit', [
                                'ref'=>$copp->ref_id,
                                'redirect'=>true,
                                'pageUrl'=>'dlbt/commentsOnPrefacePostface',
                                'page'=>$comments_on_preface_postface->currentPage(),
                            ])}}" data-placement="top" data-toggle="popover"
                                data-trigger="hover" data-content="@lang('Edit reference')">
                            <i class="fa fa-pencil text-primary"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $comments_on_preface_postface->links() }}
    </div>
@else
    <div class="text-center">
        {{-- // TODO Lang --}}
        <p>@lang('No data found.')</p>
    </div>
@endif
