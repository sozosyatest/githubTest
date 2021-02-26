<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Read extends Model
{
    //テーブル名
    protected $table = 'reads';

    protected $guarded = ["created_at", "updated_at"];
    protected $fillable = [
        'user_cd', 'daily_reports_no'
    ];

    // プライマリキーを設定する
    protected $primaryKey = 'id';
}
