<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * funknce na prihlášení uživatele do systému / případně notifikace, že pihlášení není platné
     *
     * @param Request $request
     * @return array
     */
    public function login(Request $request): array
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            if (Auth::user()->user_status == "active") {
                return [
                    'isAlert' => "isAlert",
                    'status' => "success",
                    'msg' => "Úspěšně přihlášeno",
                ];
            } else {
                Auth::logout();
                return [
                    'isAlert' => "isAlert",
                    'status' => "error",
                    'msg' => "Uživatel je zablokován!",
                ];
            }
        } else {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nesprávné údaje!",
            ];
        }
    }


    /**
     * funkce na získání informací o přihlášeném uživateli
     *
     * @return array
     */
    public function getLoggedUser()
    {
        $user = Auth::user();
        if (empty($user)) {
            return [
                'isAlert' => "isAlert",
                'status' => "error",
                'msg' => "Nejste přihlášen!",
            ];
        } else {
            return [
                'name' => $user->name,
                'user_role' => $user->user_role,
            ];
        }
    }
}
