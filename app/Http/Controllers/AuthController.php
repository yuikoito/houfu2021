<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialUser;
use Auth;
use DB;
use Illuminate\Http\Request;
use Socialite;

class AuthController extends Controller
{
    public function login()
    {
        return Socialite::with('Twitter')->redirect();
    }

    public function callback()
    {
        $providerUser = Socialite::driver('Twitter')->user();
        
        // 既に存在するユーザーかを確認
        $socialUser = SocialUser::where('provider_user_id', $providerUser->id)->first();

        if ($socialUser) {
            // 既存のユーザーはログインしてトップページへ
            Auth::login($socialUser->user, true);
            return redirect('/');
        }

        // 新しいユーザーを作成
        $user = new User();
        $user->unique_id = $providerUser->nickname;
        $user->name = $providerUser->name;
        $user->avatar = $providerUser->user['profile_image_url_https'];
        $user->bio = $providerUser->user['description'];

        $socialUser = new SocialUser();
        $socialUser->provider_user_id = $providerUser->id;

        DB::transaction(function () use ($user, $socialUser) {
            $user->save();
            $user->socialUsers()->save($socialUser);
        });

        Auth::login($user, true);
        return redirect('/');
    }
}