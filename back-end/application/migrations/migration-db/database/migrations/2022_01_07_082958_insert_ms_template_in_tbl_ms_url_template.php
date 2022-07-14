<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertMsTemplateInTblMsUrlTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $object = array(
            "type" => "ms_template", 
            "template" => '<div style=width:100%;height:20px><span style=white-space:nowrap;color:#5f5f5f;opacity:.36>________________________________________________________________________________</span></div>
            <div class=me-email-text lang=en-US style="color:#252424;font-family:"Segoe UI","Helvetica Neue",Helvetica,Arial,sans-serif"><div style=margin-top:24px;margin-bottom:20px><span style=font-size:24px;color:#252424>Microsoft Teams meeting</span></div>
            <div style=margin-bottom:20px><div style=margin-top:0;margin-bottom:0;font-weight:700><span style=font-size:14px;color:#252424>Join on your computer or mobile app</span></div><a class=me-email-headline href=%JOIN_URL% target=_blank rel="noreferrer noopener" style="font-size:14px;font-family:"Segoe UI Semibold","Segoe UI","Helvetica Neue",Helvetica,Arial,sans-serif;text-decoration:underline;color:#6264a7">Click here to join the meeting</a> </div><div style=margin-bottom:24px;margin-top:20px><a class=me-email-link target=_blank href=https://aka.ms/JoinTeamsMeeting rel="noreferrer noopener" style="font-size:14px;text-decoration:underline;color:#6264a7;font-family:"Segoe UI","Helvetica Neue",Helvetica,Arial,sans-serif">Learn More</a> | <a class=me-email-link target=_blank href=%MEETING_OPTIONS% rel="noreferrer noopener" style="font-size:14px;text-decoration:underline;color:#6264a7;font-family:"Segoe UI","Helvetica Neue",Helvetica,Arial,sans-serif">Meeting options</a> </div></div><div style="font-size:14px;margin-bottom:4px;font-family:"Segoe UI","Helvetica Neue",Helvetica,Arial,sans-serif"></div><div style=font-size:12px></div><div></div><div style=width:100%;height:20px><span style=white-space:nowrap;color:#5f5f5f;opacity:.36>________________________________________________________________________________</span></div>', 
           );
        DB::table('tbl_ms_url_template')->updateOrInsert(['type' => $object['type'],'template' => $object['template']], $object);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tbl_ms_url_template')->where("type", "ms_template")->update(["archive" => 1]);
    }
}
