@if (count($comments_on_publication) > 0)
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
            @foreach($comments_on_publication as $cop)
                <tr id="tblData-id_{{ $cop->ref_id }}">
                    @foreach ($colNames as $colName)
                        <td class="C-{{$colName}}">{{$cop->$colName}}</td>
                    @endforeach

                    <td>
                        <a href="#!" data-placement="top" data-toggle="popover" data-trigger="hover"
                            data-content="@lang('Edit attributes in modal')" data-id="{{ $cop->ref_id }}" class="edit-COP">
                            <i class="fa fa-pencil-square-o text-primary"></i>
                        </a>
                        <a href="{{action('RefController@edit', [
                                'ref'=>$cop->ref_id,
                                'redirect'=>true,
                                'pageUrl'=> strtolower(config('yarm.sys_name')) . '/commentsOnPublication',
                                'page'=>$comments_on_publication->currentPage(),
                            ])}}" data-placement="top" data-toggle="popover"
                                data-trigger="hover" data-content="@lang('Edit reference')">
                            <i class="fa fa-pencil text-primary ml-2"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $comments_on_publication->links() }}
    </div>
@else
    <div class="text-center">
        <p>@lang('No data found.')</p>
    </div>
@endif
