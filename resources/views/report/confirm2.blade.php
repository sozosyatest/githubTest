{{-- dailyreport_confirm.blade.phpからconfirm.phpへ変更 --}}
@extends('common.layout')

@section('jq_plugins','')

@section('page_js')
<script src="/js/report/confirm.js"></script>
@endsection

@section('tagu')
{{$tagu}}
@endsection

@section('title')
{{$title}}
@endsection

@section('body')
<section>
  <!-- <h1>@yield('title')</h1> -->
  <h1>日報報告</h1>
    <table border="1" class="m0a">
      <tr>
        <th>本日の作業内容</th>
        <td class="todaywork">
          {{$report -> sagyou}}
        </td>
      </tr>
      <tr>
        <th>進捗状況</th>
        <td>{{$report -> shintyoku}}</td>
      </tr>
      <tr>
        <th>残作業</th>
        <td>{{$report -> zansagyou}}</td>
      </tr>
      <tr>
        <th>引き継ぎ事項</th>
        <td>{{$report -> hikitsugi}}</td>
      </tr>
    </table>

    <h1>閲覧者一覧</h1>

    <table class="items sort-table m0a tac" border="1">
        <!--<table class="m0a tac" border="1">-->
            <thead>
                <tr> 
                    <th class="dailylist_th3">閲覧日時</th>
                    <th class="dailylist_th3">名前</th>
                </tr>
            </thead>
            <tbody>
            @if (isset($readers))
                @foreach($readers as $reader)
                <tr class="item clickable-row" data-href="/report/{{$report->no}}">
                    <td class="dailylist_td1">{{$reader -> updated_at}}</td>
                    <td class="dailylist_td2">{{$reader->user_name}}</td>
                </tr>
                @endforeach
            @endif
            </tbody>
        </table>



    <!-- 日報承認/否認は下記_20210125_kamimura -->
    <!-- <table class='btn'>
      <tr>
        <td><input class='btn' type="submit" value="承認する"></td>
        <td><input class='btn' type="submit" value="否認する"></td>
      </tr>
    </table> -->

</section>
@endsection