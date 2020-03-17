<?php

namespace App\Http\Controllers;

use App\Authenticator;
use App\Model\Github;
use App\Model\User;

class Auth extends Controller
{
    public function redirect_github(){
        session()->put('service', 'github');

        $token = Github::gettoken(\request()->code);

        if($token === false){
            return redirect('/api/signin');
        }
        session()->put('servicetoken', $token);
        return redirect('/auth/continue');
    }

    public function continue()
    {
        if (!session()->has('servicetoken'))
            return redirect('/');
        if (session()->has('servicetoken') && session()->has('logined'))
            return redirect('/info');

        if(session()->get('service') == 'github') {

            $github = Github::getuserinfo(session()->get('servicetoken'));

            $user = new User($github->login);
        }
        else{
            return response('', 501);
        }

        $a = new Authenticator();
        $title = 'pavel6520_hakaton_fTechLab';
        if ($user->qrsecret == null) {
            $user->setQRSecret($a->generateRandomSecret());
            $qrCodeUrl = $a->getQR($user->login, $user->qrsecret, $title);

            return view('continue', ['url' => $qrCodeUrl]);
        } else {
            if (isset(request()->code)) {
                if ($a->getCode($user->qrsecret) == request()->code) {
                    session()->put('logined', 1);
                    return redirect('/info');
                } else
                    return view('continue', []);
            } else
                return view('continue', []);
        }
    }
}
