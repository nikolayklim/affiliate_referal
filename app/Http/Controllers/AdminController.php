<?php
namespace App\Http\Controllers;
use App\Models\Bonous;
use App\Models\LoanSetting;
use App\User;
use App\Models\File;
use Auth;
use Illuminate\Http\Request;

class AdminController extends Controller
{  /**
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
    public function admin_dashboard()
    {
        return view('admin.summary');
    }

    public function lockscreen()
    {
        $user=User::get();

        return view('admin.passwordchange.index', compact('user'));
    }

    public function passwordupdate($id,Request $request)
    {
        // $user = Auth::user();
        $this->validate($request, [
            'email' => 'email',
            'password' => 'min:6',
            'password' => 'required|confirmed|min:6',
        ]);



        $user = User::findOrFail($id);
        $user->email= $request->get('email');
        $user->password= $request->get('password');
        // dd($user);
        $user->save();
        return Redirect()->route('admin.passwordchange.index', [$user->id])->with('message', 'Password has been updated!');
    }

    public function userProfile()
    {
        $users=User::get();
        return view('admin.userProfile.index', compact('users'));
    }

    public function userManagement()
    {
        $users=User::get();
        $referrals=Bonous::get();
        $loan=LoanSetting::get();
        $alltime_referral=$referrals[0]->alltime_referral;
        $qualified_loan=$referrals[0]->qualified_loan;
        return view('admin.userManagement.index', compact('users','referrals','qualified_loan','alltime_referral'));
    }

    public function userguid()
    {
        $files=File::get();
        return view('admin.userguid.index',compact('files'));
    }

    public function filestore(Request $request)
    {
        if ($request->hasFile('example')) 
        {
            $fileSize = $this->formatSizeUnits(filesize($request->example));
            $extension = $request->example->extension()?: 'png';
            $folderName = '/uploads';
            $safeFileName = str_random(10) . '.' . $extension;
            $fileName=$request->file('example')->getClientOriginalName();
            $request->example->move(public_path($folderName), $safeFileName);

            $files=File::create([
                'file_type'=>$extension,
                'file_name'=>$fileName,
                'safe_file_name'=>$safeFileName,
                'file_size'=>$fileSize,
                ]);
        }
            return redirect()->route('admin.userguid.index')
                            ->with('success', 'Your files has been successfully added');
    }

    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    public function destroy($id)
    {
        $image = \DB::table('files')->where('id', $id)->first();
        $file= $image->safe_file_name;
        $filename = public_path().'/uploads/'.$file;
        \File::delete($filename);

        File::find($id)->delete();
        return redirect()->back();
    }
}
