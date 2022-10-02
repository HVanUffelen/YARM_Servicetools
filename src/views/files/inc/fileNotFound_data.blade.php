@if (count($files) > 0)
    <div class="adminContent">
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
                @foreach($files as $file)
                    @php
                        $hasLocalFile = false;
                        foreach ($localFiles as $lf) {
                            if ($file->id == $lf['id']) {
                                $hasLocalFile = true;
                                $localFile = $lf;
                            }
                        }
                    @endphp
                    <tr id="tblData-id_{{ $file->id }}">
                        <td class="C-name">{{$file->name}}</td>
                        @if ($hasLocalFile)
                            <td class="C-local_name">{{$localFile['local_name']}}</td>
                        @else
                            <td class="C-local_name"></td>
                        @endif

                        <td>
                            @if ($hasLocalFile)
                                <a class="fa fa-pencil mr-1 text-primary editFile" href="#!"
                                    data-name="{{$file->name}}" data-local_name="{{$localFile['local_name']}}" data-id="{{$file->id}}"
                                    data-placement="top" data-toggle="popover" data-trigger="hover"
                                    data-content="@lang('Edit this file name')"></a>
                            @else
                                <a class="fa fa-pencil mr-1 text-primary editFile" href="#!"
                                    data-name="{{$file->name}}" data-local_name="" data-id="{{$file->id}}"
                                    data-placement="top" data-toggle="popover" data-trigger="hover"
                                    data-content="@lang('Edit this file name')"></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $files->links() }}
    </div>
@else
<div class="text-center">
    {{-- // TODO Lang GER \\ --}}
    <p>@lang('There are no files that could not be found.')</p>
</div>
@endif
