<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact.show');
    }

    public function store(Request $request)
    {
        if (!empty($request->input('website'))) {
            return redirect()->route('contact.show')->with('status', 'Tu mensaje fue enviado correctamente.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'captcha' => ['required', 'captcha'],
        ]);

        Contact::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('contact.show')->with('status', 'Tu mensaje fue enviado correctamente.');
    }
}