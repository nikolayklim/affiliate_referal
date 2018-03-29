<?php

namespace App\Http\Controllers\Auth;

use App\User;

use Illuminate\Http\Request;
use App\Models\ActivationService;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{

    protected $activationService;

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ActivationService $activationService)
    {
        $this->middleware('guest');

        $this->activationService = $activationService;
    }
    
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) 
         {
        //     $this->throwValidationException(  $request, $validator );
        }
        $user = $this->create($request->all());

        $this->activationService->sendActivationMail($user);

        return redirect()->route('email.checkgmail');
    }

    public function activateUser($token)
    {
        if ($user = $this->activationService->activateUser($token)) 
        {
            auth()->login($user);

            // return redirect($this->redirectPath());
            return redirect()->route('email.ewallet');
        }
        abort(404);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'birthday' => 'required|date|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phoneNumber' => 'required|regex:/(01)[0-9]{9}/',
            'whatsappNumber' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    
    protected function create(array $data)
    {
        return User::create([
            'fullname' => $data['fullname'], 
            'username' => $data['username'], 
            'birthday' => $data['birthday'],
            'country' => $data['country'], 
            'state' => $data['state'], 
            'email' => $data['email'],
            'address' => $data['address'],
            'phoneNumber' => $data['phoneNumber'],
            'whatsappNumber' => $data['whatsappNumber'], 
            'password' => bcrypt($data['password']),
        ]);

        event(new \App\Events\UserReferred(request()->cookie('ref'), $user));

        return $user;
    }


    public function verify($token)
    {
        $user = User::where('email_token', $token)->first();
        if(count($user) > 0){
            $user->verified = 1;
            $user->email_token = '';
            if($user->save()){
                return redirect('login')->with('activated', 'Your Email is successfully verified.');
            }
        }
        else{
            return redirect('login')->with('verified', 'Your Email is already verified');
        }
    }
}
