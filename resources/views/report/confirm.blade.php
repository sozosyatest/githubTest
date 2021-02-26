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
  <form action="{{route('report.store')}}" method="post">
    @csrf
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
    <input type="hidden" name="sagyou" value="{{$report -> sagyou}}">
    <input type="hidden" name="shintyoku" value="{{$report-> shintyoku }}">
    <input type="hidden" name="zansagyou" value="{{$report -> zansagyou}}">
    <input type="hidden" name="hikitsugi" value="{{$report -> hikitsugi}}">
    <!-- 新規日報登録/編集は下記_20210125_kamimura -->
    <div class='btn_box tac'>
    <table class='btn'>
      <tr>
        <td><input class='btn btn1' type="submit" name="submit" value="登録する"></td>
        <td><input class='btn btn2' type="submit" name="submit" value="修正する"></td>
      </tr>
    </table>
    </div>

    <!-- 日報承認/否認は下記_20210125_kamimura -->
    <!-- <table class='btn'>
      <tr>
        <td><input class='btn' type="submit" value="承認する"></td>
        <td><input class='btn' type="submit" value="否認する"></td>
      </tr>
    </table> -->

  </form>
</section>
@endsection