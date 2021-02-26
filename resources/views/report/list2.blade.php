{{-- dailylist.blade.phpからlist.blade.phpに変更 --}}
@extends('common.layout')

@section('jq_plugins')
<script src="/js/jquery.pagination.js"></script> 
<script src="/js/jquery.tablesorter.min.js"></script>
@endsection

@section('page_js')
<script src="/js/report/list.js"></script>
@endsection

@section('tagu')
  {{$tagu}}
@endsection

@section('title1')
  {{$title1}}
@endsection



@section('body')
    <section>
        <form action="{{url('/reportlist')}}" method="POST">
        @csrf
            <select name="user">
                <option value=0>全件取得</option>
                @foreach($users as $user)
                    @if($user->user_cd == $search['user'])
                        <option value="{{$user->user_cd}}" selected>{{$user->user_name}}</option>
                    @else
                        <option value="{{$user->user_cd}}">{{$user->user_name}}</option>
                    @endif
                @endforeach
            </select>
            @if($search['new'] == true)
            <p><input type="checkbox" name="new" value=true checked="checked">新着<input type="text" name="newdays" value="{{$search['newdays']}}">日前</p>
            @else
            <p><input type="checkbox" name="new" value=true>新着<input type="text" name="newdays" value="{{$search['newdays']}}">日前</p>
            @endif
            <p><input type="text" name="sagyou" value="{{$search['sagyou']}}">内容</p>
            <p><input type="date" name="after" value="{{$search['after']}}">から<input type="date" name="before" value="{{$search['before']}}">まで</p>
            <p><input type="submit" value="検索"></p>
        </form>
    </section>
    <section>
	    <h1>@yield('title1')</h1>
        <table class="items sort-table m0a tac" border="1">
        <!--<table class="m0a tac" border="1">-->
            <thead>
                <tr> 
                    <th>報告日</th>
                    <th>件名</th>
                    <th>未読</th>
                    <th class="dailylist_th3">名前</th>
                </tr>
            </thead>
            <tbody>
            @if (isset($data))
                @foreach($data as $report)
                <tr class="item clickable-row" data-href="/report/{{$report->no}}">
                    <td class="dailylist_td1">{{$report -> created_at}}</td>
                    <td class="dailylist_td2 dailylist">{{Str::limit($report -> sagyou, 25)}}</td>
                    @if ($report->read == 'read')
                        <td class="dailylist_td1">既読</td>
                    @elseif ($report->read == 'notread')
                        <td class="dailylist_td1">未読</td>
                    @else
                        <td class="dailylist_td1">自身</td>
                    @endif
                    <td class="dailylist_td3">{{$report -> user_name}}</td>
                </tr>
                @endforeach
            @endif
            </tbody>
        </table>
        <!-- 202画面にて遷移ボタンがある為修正、コメントアウト対応_210125_kamimura-->
        <!-- <p class="btn01"><a href="{{route('report.create')}}"></a></p> -->
    </section>
@endsection

