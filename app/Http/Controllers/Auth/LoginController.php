<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Models\ActivationService;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    

    protected $activationService;

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    
    public function __construct(ActivationService $activationService)
    {
        $this->middleware('guest')->except('logout');;
        
        $this->activationService = $activationService;
    }


    public function authenticated(Request $request, $user)
    {
        if (!$user->activated) 
        {
            $this->activationService->sendActivationMail($user);
            
            auth()->logout();

            return back()->with('warning', 'You need to confirm your account. We have sent you an activation code, please check your email.');
        }
        return redirect()->intended($this->redirectPath());
    }

    public function activateUser($token)
    {
        if ($user = $this->activationService->activateUser($token)) 
        {
            auth()->login($user);
            
            return redirect($this->redirectPath());
        }
        abort(404);
    }
}
