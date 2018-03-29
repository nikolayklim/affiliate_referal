<?php

namespace App\Http\Controllers;
use App\User;
use Auth;
use App\ReferralContestSetting;
use App\Models\LoanSetting;
use Illuminate\Http\Request;

class ReferralSettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function referralSetting()
    {
        $users=User::get();
        
        return view('admin.referralSetting.index',compact('users'));
    }
}
