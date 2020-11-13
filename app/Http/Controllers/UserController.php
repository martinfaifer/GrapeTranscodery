<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{

    public $roles = [
        array('role' => "admin"),
        array('role' => "hotline"),
        array('role' => "nahled")
    ];

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
    public static function getLoggedUser()
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


    /**
     * fn pro výpis všech uživatelů
     *
     * @return array
     */
    public function users(): array
    {
        if (!User::first()) {
            return [
                'status' => "empty"
            ];
        }

        return User::get()->toArray();
    }


    /**
     * výpis uživatelských rolí
     *
     * @return array
     */
    public function users_role(): array
    {
        return $this->roles;
    }


    /**
     * fn pro zallzení uzivatele
     *
     * @param Request $request -> heslo , role, jmeno , mail
     * @return array
     */
    public function create_user(Request $request): array
    {

        // validace
        if (
            is_null($request->heslo) || empty($request->heslo) ||
            is_null($request->role) || empty($request->role) ||
            is_null($request->jmeno) || empty($request->jmeno) ||
            is_null($request->mail) || empty($request->mail)
        ) {

            return [
                'status' => "warning",
                'msg' => "Není vše vyplněno"
            ];
        }

        if (User::where('email', $request->mail)->first()) {
            return [
                'status' => "warning",
                'msg' => "Email již existuje!"
            ];
        }

        if (!filter_var($request->mail, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => "warning",
                'msg' => "E-mail má neplatný formát"
            ];
        }

        try {
            User::create([

                'name' => $request->jmeno,
                'email' => $request->mail,
                'user_role' => $request->role,
                'user_status' => "active",
                'password' => Hash::make($request->heslo)
            ]);

            return [
                'status' => "success",
                'msg' => "Založeno"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se založit"
            ];
        }
    }


    /**
     * fn pro vyhledání užiavatele
     *
     * @param Request $request userId
     * @return array
     */
    public static function search_user(Request $request): array
    {
        if (empty($request->userId) || is_null($request->userId)) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se vyhledat!"
            ];
        }

        if (!User::where('id', $request->userId)->first()) {
            return [
                'status' => "error",
                'msg' => "Neexistujici uživatel"
            ];
        }

        return User::where('id', $request->userId)->first()->toArray();
    }


    /**
     * fn pro editaci uživatele
     *
     * @param Request $request
     * @return array
     */
    public function edit_user(Request $request): array
    {
        if (empty($request->userId) || is_null($request->userId)) {
            return [
                'status' => "error",
                'msg' => "Nebyl nalezen uživatel"
            ];
        }

        if (
            empty($request->jmeno) || is_null($request->jmeno) ||
            empty($request->mail) || is_null($request->mail)

        ) {
            return [
                'status' => "warning",
                'msg' => "Není řádně vyplněno"
            ];
        }

        try {
            // overení zda je moznost editovat heslo
            if ($request->editHeslo) {
                if (!is_null($request->heslo) || !empty($request->heslo)) {
                    User::where('id', $request->userId)->update([
                        'password' => Hash::make($request->heslo)
                    ]);
                }
            }

            if (!empty($request->userStatus) || !is_null($request->userStatus)) {
                User::where('id', $request->userId)->update(['user_status' => $request->userStatus]);
            }

            if (!empty($request->role) || !is_null($request->role)) {
                User::where('id', $request->userId)->update(['user_role' => $request->role]);
            }

            User::where('id', $request->userId)->update([
                'name' => $request->jmeno,
                'email' => $request->mail
            ]);


            return [
                'status' => "success",
                'msg' => "Editováno"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se editovat"
            ];
        }
    }

    /**
     * fn pro odebrani uzivatele
     *
     * @param Request $request
     * @return array
     */
    public function delete_user(Request $request): array
    {
        if ($request->userId === Auth::user()->id) {
            return [
                'status' => "warning",
                'msg' => "Nelze smazat sam sebe"
            ];
        }

        try {
            $user = User::where('id', $request->userId)->first();
            $user->delete();
            return [
                'status' => "success",
                'msg' => "Odebrano"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Nepodařilo se odebrat"
            ];
        }
    }

    /**
     * fn pro zmenu hesla
     *
     * @param Request $request
     * @return array
     */
    public function update_password(Request $request): array
    {

        if (is_null($request->heslo) || empty($request->heslo)) {
            return [
                'status' => "warning",
                'msg' => "Pole nesmí být prázdné"
            ];
        }
        $user = Auth::user();

        try {
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->heslo)
            ]);

            return [
                'status' => "success",
                'msg' => "Heslo změněno"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => "error",
                'msg' => "Heslo se nepodařilo změnit"
            ];
        }
    }
}
