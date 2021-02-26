<?php

namespace App\Http\Controllers;

use App\Daily_report;
use App\Read;
use Illuminate\Http\Request;
use App\Http\Requests\ReportRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    public function __construct()
    {
        // 日報表示にauthミドルウェアを適用
        $this->middleware('auth');
    }

    /**
     * 本人の投稿かどうかを判定する
     * 
     * @param mixed $data 日報データ
     * @return boolean true=>上司 false=>投稿者本人 null 第三者もしくは日報noなし
     */

    public static function isAuth($data)
    {
        //ログインIDを取得
        $current = Auth::id();
        // ログイン者と記事内IDを比較
        if (isset($data->post_user_cd)) {
            if ($current == $data->post_user_cd) {
                return true; // 本人です
            } else {
                // 当該日報に関係ない第三者に直接リクエストされた場合の対応
                return null;
            }
        } else {
            // 存在しない日報idをリクエストされた場合の対応
            return null;
        }
    }

    /**
     * 日報一覧を表示する
     *
     * @return view [203]日報一覧確認画面
     */

    public function index()
    {
        //未承認の自分の日報を取得する
        $current = Auth::id();
        $table = DB::table('daily_reports');
        $reports = DB::select(DB::raw("SELECT dr.no, dr.created_at,REPLACE(LEFT(dr.sagyou, 12), '\r\n', ' ') sagyou, st.name
        FROM daily_reports dr
        LEFT JOIN v_user_info vui
        ON dr.post_user_cd = vui.user_cd
        LEFT JOIN statuses st
        ON dr.status = st.cd
        WHERE dr.status < 1
        AND dr.post_user_cd = :cd
        ORDER BY dr.no"), ['cd' => $current]);

        // 承認済みの自分の日報を取得する
        $table = DB::table('daily_reports');
        $reports2 = DB::select(DB::raw(
            "SELECT dr.no, dr.created_at,REPLACE(LEFT(dr.sagyou, 12), '\r\n', ' ') sagyou, st.name
        FROM daily_reports dr
        LEFT JOIN v_user_info vui
        ON dr.post_user_cd = vui.user_cd
        LEFT JOIN statuses st
        ON dr.status = st.cd
        WHERE dr.status = 1
        AND dr.post_user_cd = :cd
        ORDER BY dr.no"
        ), ['cd' => $current]);

        // viewに渡すblade用データ
        $tagu = '日報';
        $title1 = '日報承認・確認';
        $title2 = '日報一覧';
        $css = 'dailylist.css';

        // viewを呼び出す
        return view('report.list', compact('reports', 'reports2', 'tagu', 'title1', 'title2',  'css'));
    }

    /**
     * 日報登録フォームを表示する
     * 
     * @return view [204]個別日報入力画面
     */

    public function create()
    {
        // viewに渡すblade用データ
        $title = '日報登録';
        $css = 'dailyreport.css';
        $report = (object)[
            'sagyou' => '',
            'shintyoku' => '',
            'zansagyou' => '',
            'hikitsugi' => '',
        ];
        \Session::forget('post_cd');
        \Session::forget('post_no');

        // viewを呼び出す
        return view('report.dailyreport', compact('report', 'title', 'css'));
    }

    /**
     * 日報登録の確認画面を表示する
     * 
     * @param Reportrequest $request バリデーション済の日報入力データ
     * @return view [205]個別日報入力内容確認画面
     */

    public function confirm(ReportRequest $request)
    {
        // 入力データを受け取る
        $report = $request->input();
        \Session::put('report', $report);

        // reportオブジェクトに入力データを代入
        $report = (object)[
            'sagyou' => $report['sagyou'],
            'shintyoku' => $report['shintyoku'],
            'zansagyou' => $report['zansagyou'],
            'hikitsugi' => $report['hikitsugi'],
            'post_user_cd' => \Session::get('post_cd'),
            'auth_user_cd' => \Session::get('auth_cd'),
            'status' => -1,
        ];

        // viewに渡すblade用データ
        $tagu = '日報登録確認';
        $title = '日報登録確認';
        $css = 'dailyreport_confirm.css';

        // viewを呼び出す
        return view('report.confirm', compact('report', 'tagu', 'title', 'css'));
    }

    /**
     * 日報登録を実行する
     *
     * @param  Request $request 確認画面からの入力データ
     * @return view [211]個別日報入力内容完了画面
     */

    public function store(Request $request)
    {

        // 入力フォームの情報を受取る
        $input = $request->input();

        // [修正する]ボタンを押下したら入力画面に戻る
        if ($input['submit'] == '修正する') {
            return redirect('report/create')->withInput();
        } else {
            $data = \Session::get('report', 'データが存在しません');
            $data['post_cd'] = \Session::get('post_cd');
            \Session::forget('post_cd');
            if (isset($data['post_cd'])) {
                $data['no'] =  \Session::get('post_no');
                \Session::forget('post_no');
            }

            // 日報noの存在でCREATEとUPDATEを分岐する
            if (!isset($data['no'])) {
                //日報noが無ければ新規登録する
                \DB::beginTransaction();
                try {
                    $max_no = DB::table('daily_reports')->max('no');
                    $current = Auth::id();
                    $joushi = DB::select('SELECT f_get_joushi(:cd) joushi', ['cd' => $current]);
                    foreach ($joushi as $jo) {
                        $jocd = $jo->joushi;
                    }

                    Daily_report::create([
                        'no' => $max_no + 1,
                        'post_user_cd' => $current,
                        'auth_user_cd' => $jocd,
                        'sagyou' => $data['sagyou'],
                        'shintyoku' => $data['shintyoku'],
                        'zansagyou' => $data['zansagyou'],
                        'hikitsugi' => $data['hikitsugi'],
                        'status' => 0,
                    ]);
                    \DB::commit();
                } catch (\Throwable $th) {
                    // 登録失敗の場合はロールバックする
                    \DB::rollback();
                    dd($th);
                    abort(500);
                }
            } else {
                //日報noがある場合は編集を実行する
                \DB::beginTransaction();
                try {
                    $report = Daily_report::find($data['no']);
                    $report->fill([
                        'sagyou' => $data['sagyou'],
                        'shintyoku' => $data['shintyoku'],
                        'zansagyou' => $data['zansagyou'],
                        'hikitsugi' => $data['hikitsugi'],
                        'status' => 0,
                    ]);
                    $report->save();
                    \DB::commit();
                } catch (\Throwable $th) {
                    // 登録失敗の場合はロールバックする
                    \DB::rollback();
                    abort(500);
                }
            }

            // viewを呼び出す
            return redirect(route('report.complete'));
        }
    }

    /**
     * 日報の個別記事を表示する
     *
     * @param int $id 日報no
     * @return view [205] 個別日報入力内容確認画面
     */

    public function show($id)
    {
        // 日報を取得する
        $report = Daily_report::find($id);
        if (is_null($report)) {
            \Session::flash('err_msg', 'データがありません');
            return redirect(route('report.index'));
        }

        // ↓ 既読処理用にいじります 松田

        \DB::beginTransaction();
        try {
            $current = Auth::id();
            $read = Read::where('user_cd', $current)
                ->where('daily_reports_no', $id)
                ->first();
            if(empty($read)){
                $read = Read::create([
                    'user_cd' => $current,
                    'daily_reports_no' => $id,
                ]);
            }else{
                $data = [
                    'user_cd' => $current,
                    'daily_reports_no' => $id,
                ];
                $read->update($data);
            }
            
            \DB::commit();
        } catch (\Throwable $th) {
            // 登録失敗の場合はロールバックする
            \DB::rollback();
            dd($th);
            abort(500);
        }

        // ↑ ここまで


        // 既読者一覧
        $readers = Read::select('reads.user_cd', 'vui.user_name', 'reads.updated_at')
        ->leftjoin('v_user_info as vui', 'reads.user_cd', '=', 'vui.user_cd')
        ->where('daily_reports_no', $id)
        ->get();

        // セッションに記事noを登録
        \Session::put('post_no', $report->no);

        // viewに渡すblade用データ
        $tagu = '日報登録確認';
        $title = '日報登録確認';
        $css = 'dailyreport_confirm.css';


        // viewを呼び出す
        return view('report.confirm2', compact('report', 'tagu', 'title', 'css', 'readers'));
    }


    /**
     * 日報差戻しの編集フォームを表示する
     *
     * @param int $id 日報no
     * @return view [204]個別日報入力画面
     */

    public function edit($id)
    {
        // 日報を取得する
        $report = Daily_report::find($id);
        if (is_null($report)) {
            \Session::flash('err_msg', 'データがありません');
            return redirect(route('report.index'));
        }

        // 本人以外が日報にアクセスした場合は閲覧できないようにする
        $is_auth = DailyReportController::isAuth($report);
        if (is_null($is_auth)) {
            return redirect(route('report.index'));
        }
        \Session::put('auth_cd', $report->auth_user_cd);
        \Session::put('post_cd', $report->post_user_cd);
        \Session::put('post_no', $report->no);

        // viewに渡すblade用データ
        $title = '日報編集';
        $css = 'dailyreport.css';

        // viewを呼び出す
        return view('report.dailyreport', compact('report', 'title', 'css'));
    }

    /**
     * 日報登録完了画面を表示する
     *
     * @return view [211]個別日報登録完了画面
     */

    public function complete()
    {
        // viewに渡すblade用データ
        $tagu = '日報登録完了';
        $css = 'usertouroku.css';

        // viewを呼び出す
        return view('report.complete', compact('tagu', 'css'));
    }


    /**
     * 日報を削除する(未実装)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Respons
     */
    public function destroy($id)
    {
        // if (empty($id)) {
        //     \Session::flash('err_msg', 'データがありません');
        //     return redirect(route(''));
        // }
        // try {
        //     Report::destroy($id);
        // } catch (\Throwable $e) {
        //     abort(500);
        // }
        // \Session::flash('err_msg', '記事を削除しました');
        // return redirect(route(''));
    }
}