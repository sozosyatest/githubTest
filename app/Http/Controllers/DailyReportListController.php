<?php

namespace App\Http\Controllers;

use App\Daily_report;
use App\Read;
use App\User;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;
use App\Http\Requests\ReportRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyReportListController extends Controller
{
    public function __construct()
    {
        // 日報表示にauthミドルウェアを適用
        $this->middleware('auth');
    }

    
    /**
     * getアクセス時、自分の日報一覧を表示する
     *
     * @return view [203]日報一覧確認画面
     */

    public function index(Request $request)
    {

        $current = Auth::id();
        $table = DB::table('daily_reports');
        $search = [
            'user' => "",
            'client' => "",
            'area' => "",
            'sagyou' => "",
            'new' => "",
            'newdays' => "",
            'after' => "",
            'before' => "",
        ];

        // 日報検索用のクエリ作成、$dataに格納
        $query = Daily_report::query();
        $query->select(
            'dr.*',
            'vui.dep_div_name', 
            'vui.user_name')
        ->from('daily_reports as dr')
        ->leftjoin('v_user_info as vui', 'dr.post_user_cd', '=', 'vui.user_cd')
        ->where('dr.post_user_cd', '=', $current)
        ->orderBy('dr.created_at', 'desc');
    
        $data = $query->get();
        foreach($data as $d){
            $d['read'] = 'mine';
        }

        // user検索のプルダウンメニュー表示用
        $users = DB::select(DB::raw("SELECT vui.user_cd, vui.user_name
        FROM v_user_info vui
        ORDER BY vui.user_cd"));


        // 必要なもの：訪問先一覧、エリア一覧、

        // viewに渡すblade用データ
        $tagu = '日報';
        $title1 = \Session::get('username').' 日報一覧';
        $css = 'dailylist.css';

        // viewを呼び出す
        return view('report.list2', compact('tagu', 'title1', 'css', 'data', 'users', 'search'));
    }


    /**
     * postアクセス時、検索に応じて日報一覧を表示する
     *
     * @return view [203]日報一覧確認画面
     */

    public function search(Request $request)
    {

        $current = Auth::id();
        $table = DB::table('daily_reports');
        $query = Daily_report::query();

        $search_user = $request->input('user');
        $search_client = $request->input('client');
        $search_area = $request->input('area');
        $search_sagyou = $request->input('sagyou');
        $search_new = $request->input('new');
        $search_newdays = $request->input('newdays');
        $search_after = $request->input('after');
        $search_before = $request->input('before');
        $search = [
            'user' => $search_user,
            'client' => $search_client,
            'area' => $search_area,
            'sagyou' => $search_sagyou,
            'new' => $search_new,
            'newdays' => $search_newdays,
            'after' => $search_after,
            'before' => $search_before,
        ];
        

        // 日報検索用のクエリ作成
        $query
        ->distinct()
        ->select(
            'dr.*',
            'vui.dep_div_name', 
            'vui.user_name',
            'reads.user_cd',
            )
        ->from('daily_reports as dr')
        ->leftjoin('v_user_info as vui', 'dr.post_user_cd', '=', 'vui.user_cd')
        ->join('reads', 'vui.user_cd', '=', 'reads.user_cd')
        ->orderBy('dr.created_at', 'desc');


        // 検索条件に応じてクエリにwhere句追加
        // user検索
        if(!empty($search_user)){
            if($search_user == 0){
                // 全件表示の場合、何もしない
            }else{
                // それ以外の場合、作成者名で検索
                $query
                ->where('dr.post_user_cd', '=', $search_user);
            }
        }
        // 作業内容であいまい検索
        if(!empty($search_sagyou)){
            $query
            ->where('sagyou', 'like', '%'.$search_sagyou.'%');
        }
        // 日時検索（以降）
        if(!empty($search_after)){
            $query
            ->where('dr.created_at', '>=', $search_after);
        }
        // 日時検索（以前）
        if(!empty($search_before)){
            $search_before = date("Y/m/d H:i:s", strtotime("$search_before 1 day"));
            $query
            ->where('dr.created_at', '<=', $search_before);
        }
        // 新着検索(期間条件)
        if(!empty($search_new)){
            // とりあえずログインユーザーのコードにしてる、DBで新着日数カラムを作成して指定したい
            // $subday = User::select('cd')->where('cd', $current)->value('cd');
            if (\Cookie::has('new')) {
                $subday = \Cookie::get('new');
            }else{
                $subday = 3;
            }
            $day = Carbon::today()->subDays($subday);
            $query->where('dr.created_at', '>=', $day);
        }
        // データ取得
        $data = $query->get();


        // 未読既読の処理
        foreach($data as $d){
            // 既読テーブルに存在する場合：既読
            if(Read::where('user_cd', $current)->where('daily_reports_no', '=', $d->no)->exists()){
                // 既読の場合read
                $d['read'] = 'read';
            }else{
                // 未読の場合notread
                $d['read'] = 'notread';
            }

            if($d['post_user_cd'] == $current){
                // 自分の場合mine
                $d['read'] = 'mine';
            }

        }

        // 新着検索（未読条件）
        if(!empty($search_new)){
            // 未読の検索
            $data = $data->where('read', 'notread');
        }


        // user検索のプルダウンメニュー表示用
        $users = DB::select(DB::raw("SELECT vui.user_cd, vui.user_name
        FROM v_user_info vui
        ORDER BY vui.user_cd"));

        // 必要なもの：訪問先一覧、エリア一覧、

        // viewに渡すblade用データ
        $tagu = '日報';
        $title1 = '日報一覧';
        $css = 'dailylist.css';

        // viewを呼び出す
        return view('report.list2', compact('tagu', 'title1', 'css', 'data', 'users', 'search'));
    }




}
