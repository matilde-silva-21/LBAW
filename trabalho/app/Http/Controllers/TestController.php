<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Added to support email sending.
use Mail;
use App\Mail\MailtrapExample;


class TestController extends Controller
{
    // sendEmail method.
    public function sendEmail() {

        $mailData = [
            'name' => 'User Name',
            'email' => 'user.email@example.com', // Change to your email for testing.
        ];

        Mail::to($mailData['email'])->send(new MailtrapExample($mailData));
           
        dd("Email was sent successfully.");
    }
}

