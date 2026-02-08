<?php

namespace App\Services;

use App\Models\UserVerification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class MailService
{
    // public static function sendMailCreateAccount($user)
    // {
    //     $email = $user->email;
    //     Mail::send('mails.add-account', ['user' => $user], function ($message) use ($email) {
    //         $message->to($email)
    //             ->subject('🎉 Tài khoản học viên đã được tạo!');
    //     });
    //     //dd($user);
    // }

    // public static function sendMailResetPassword($user, $resetLink)
    // {
    //     $email = $user->email;
    //     Mail::send('mails.form-send-reset-password', ['user' => $user, 'resetLink' => $resetLink], function ($message) use ($email) {
    //         $message->to($email)
    //             ->subject('🔐 Đặt lại mật khẩu của bạn');
    //     });
    // }


    public static function sendMailRegisterAccount($user)
    {
        $verificationCode = strtoupper(Str::random(6));
        UserVerification::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'code' => $verificationCode,
            'expires_at' => Carbon::now()->addMinutes(UserVerification::EXPIRED_AT),

        ]);
        Mail::send('mails.register-account', ['user' => $user, 'code' => $verificationCode], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('🎉 Chào mừng bạn đến với Ticket!');
        });
    }

    public static function sendMailForgotPassword($user)
    {
        $email = $user->email;
        $token = Str::random(6);
        UserVerification::updateOrCreate(
            [
                'user_id' => $user->id,

            ],
            [
                'code' => $token,
                'expires_at' => Carbon::now()->addMinutes(UserVerification::EXPIRED_AT),
            ]
        );

        Mail::send('mails.forgot_password', ['name' => $user->name, 'token' => $token], function ($message) use ($email) {
            $message->to($email)
                ->subject('🔐 Đặt lại mật khẩu của bạn');
        });
    }
}
