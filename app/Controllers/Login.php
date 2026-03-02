<?php

namespace App\Controllers;

class Login extends BaseController
{
    public function index()
    {
        return redirect()->to('/login');
    }

    public function auth()
    {
        return redirect()->to('/auth');
    }

    public function dashboard()
    {
        return redirect()->to('/dashboard');
    }
}
