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
                // some code here
                return NotificationController::notify("success", "success", "Přihlášeno!");
            } else {
                Auth::logout();
                return NotificationController::notify("error", "error", "Uživatel je zablokován!");
            }
        } else {
            return NotificationController::notify("error", "error", "Nesprávné údaje!");
        }
    }

    /**
     * odhlášení uživatele
     *
     * @return void
     */
    public function lognout()
    {
        Auth::logout();
    }

    /**
     * funkce na získání informací o přihlášeném uživateli
     *
     * @return array
     */
    public static function getLoggedUser()
    {

        if (!$user = Auth::user()) {
            return NotificationController::notify("error", "error", "Nejste přihlášen!");
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
            return NotificationController::notify("warning", "warning", "Není vše vyplněno!");
        }

        if (User::where('email', $request->mail)->first()) {
            return NotificationController::notify("warning", "warning", "Email již existuje!");
        }

        if (!filter_var($request->mail, FILTER_VALIDATE_EMAIL)) {
            return NotificationController::notify("warning", "warning", "E-mail má neplatný formát!");
        }

        try {
            User::create([

                'name' => $request->jmeno,
                'email' => $request->mail,
                'user_role' => $request->role,
                'user_status' => "active",
                'password' => Hash::make($request->heslo)
            ]);

            return NotificationController::notify("success", "success", "Založeno!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
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
            return NotificationController::notify("error", "error", "Nepodařilo se vyhledat uživatele!");
        }

        if (!User::where('id', $request->userId)->first()) {
            return NotificationController::notify("error", "error", "Nepodařilo se vyhledat uživatele!");
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
            return NotificationController::notify("error", "error", "Nepodařilo se vyhledat uživatele!");
        }

        if (
            empty($request->jmeno) || is_null($request->jmeno) ||
            empty($request->mail) || is_null($request->mail)

        ) {
            return NotificationController::notify("warning", "warning", "Není vše řádně vyplněno!");
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

            return NotificationController::notify("success", "success", "Editováno!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
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
            return NotificationController::notify("warning", "warning", "Nelze smazat sám sebe!");
        }

        try {
            $user = User::where('id', $request->userId)->first();
            $user->delete();
            return NotificationController::notify("success", "success", "Odebráno!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
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
            return NotificationController::notify("warning", "warning", "Pole nesmí být prázdné!");
        }
        $user = Auth::user();

        try {
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->heslo)
            ]);

            return NotificationController::notify("success", "success", "Heslo změněno!");
        } catch (\Throwable $th) {
            return NotificationController::notify();
        }
    }
}
