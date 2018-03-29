<?php

namespace App\Models;
use Auth;
use App\User;
use Mail;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Database\Eloquent\Model;

class ActivationService extends Model
{
    protected $mailer;

    protected $activationRepo;

    protected $resendAfter = 24;

    public function __construct(Mailer $mailer, ActivationRepository $activationRepo)
    {
        $this->mailer = $mailer;
        
        $this->activationRepo = $activationRepo;
    }

    public function sendActivationMail($user)
    {

        if ($user->activated || !$this->shouldSend($user)) 
        {
            return;
        }

        $token = $this->activationRepo->createActivation($user);

        $link = route('user.activate', $token);


        // Mail::send('email.Inbox', ['user' => $link], function ($m) use ($user)
        // {
        //     $m->from('admin@app.com', 'Auth Application');
        //     $m->to($user->email, $user->name)->subject('Verify Account');
        // });
        // return redirect()->back()->with('registered', 'Check your email to confirm account.');


        $message = sprintf('Activate account <a href="%s">%s</a>', $link, $link);

        $this->mailer->raw($message, function (Message $m) use ($user) 
        {
            $m->to($user->email)->subject('Activation mail');
        });

    }

    public function activateUser($token)
    {
        $activation = $this->activationRepo->getActivationByToken($token);

        if ($activation === null) {
            return null;
        }

        $user = User::find($activation->user_id);

        $user->activated = true;

        $user->save();

        $this->activationRepo->deleteActivation($token);

        return $user;

    }

    private function shouldSend($user)
    {
        $activation = $this->activationRepo->getActivation($user);

        return $activation === null || strtotime($activation->created_at) + 60 * 60 * $this->resendAfter < time();
    }

}
