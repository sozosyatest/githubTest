<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//メイン画面を表示する
Route::get('/', 'HomeController@main')->name('main');

// // <<日報管理関係ルーティング>>
// 登録確認画面を表示 -> confirm
Route::post('report/confirm', 'DailyReportController@confirm')->name('report.confirm');
// 登録完了画面を表示 -> complete
Route::get('report/complete', 'DailyReportController@complete')->name('report.complete');
Route::resource('report', 'DailyReportController');
// 一覧を表示 => index
// 登録画面を表示 => create
// 登録実行 => store
// 差戻しの編集画面を表示 => edit
// 削除する => delete
// 個別の記事を表示 => show


// 検索付き日報一覧松田用
Route::get('reportlist', 'DailyReportListController@index')->name('reportlist.index');
Route::post('reportlist', 'DailyReportListController@search')->name('reportlist.search');

// メール送信用
Route::get('/mail', 'MailSendController@send');

// <<ユーザー管理関係ルーティング>>
Auth::routes();
Route::get('auth/logout', 'Auth\LoginController@logout')->name('user.logout');

// システム管理者のみアクセス可能
Route::group(['middleware' => 'admin'], function () {
    // この中は、全てミドルウェアが適用されます。
    Route::post('auth/confirm', 'Auth\RegisterController@confirm')->name('user.confirm');
    Route::get('register', 'Auth\RegisterController@create')->name('user.create');
    Route::post('store', 'Auth\RegisterController@store')->name('user.store');
    Route::get('auth/edit/{id}', 'Auth\RegisterController@edit')->name('user.edit');
    Route::get('auth/complete', 'Auth\RegisterController@complete')->name('user.complete');
    Route::get('auth/admin', 'Auth\RegisterController@admin')->name('user.admin');
});


// <<ユーザー情報取得ルーティング>>
Route::get('get_div/{dep_cd}', 'Auth\GetinfoController@getDiv');
Route::get('get_user/dep/{dep_cd}', 'Auth\GetinfoController@getUserByDep');
Route::get('get_user/div/{dep_cd}/{div_cd}', 'Auth\GetinfoController@getUserByDiv');