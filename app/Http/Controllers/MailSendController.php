<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Daily_report;
use App\Mail\SendMail;
use Carbon\Carbon;
use Mail;

class MailSendController extends Controller
{
    //
    public function send(){
        // usersテーブルにemailカラムを追加してから
        // $to = User::all();
    
        // Mail::to($to)->send(new SendMail());


        // テスト用に、自分のアドレス(送信先)
        $tome = [
            [
                'email' => 'pillopillo7123@gmail.com', 
                'name' => 'to name',
            ]
        ];

        // 本日0:00以降に更新、作成された日報のnoを取得
        $day = Carbon::today();
        $reports = Daily_report::select('no')->where('updated_at', '>=', $day)->get();

        Mail::to($tome)->send(new SendMail($reports));

        
    }

}
