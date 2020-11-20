<?php

namespace App\Http\Controllers;

use App\Mail\IssueStream;
use App\Mail\SuccessStream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * fn pro odeslání mail alertu s informací o nefunkčním streamu
     *
     * @param string $stream
     * @return void
     */
    public static function send_error_stream(string $stream): void
    {
        foreach (User::get() as $user) {
            Mail::to($user->email)->send(new IssueStream($stream));
        }
    }

    /**
     * fn pro odeslání notifikace o uspesnem automatickem psusteni streamu
     *
     * @param string $stream
     * @return void
     */
    public static function send_success_stream(string $stream): void
    {
        foreach (User::get() as $user) {
            Mail::to($user->email)->send(new SuccessStream($stream));
        }
    }
}
