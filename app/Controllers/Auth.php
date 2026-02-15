<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LogModel;
use CodeIgniter\Controller;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        // Check if lockout expiry is active
        $lockout = 0;
        $expiry = session()->get('lockout_expiry');

        if ($expiry && time() < $expiry) {
            $lockout = $expiry - time();
        } else {
            session()->remove('lockout_expiry');

            // Once the lockout time is over, delete failed login attempts based on IP
            $this->clearFailedAttempts();
        }

        return view('login', ['lockout' => $lockout]);
    }

   public function auth()
{
    $session = session();
    $model = new UserModel();
    $db = \Config\Database::connect();

    // Sanitize input
    $ID = filter_var($this->request->getPost('ID'), FILTER_SANITIZE_STRING);
    $password = trim($this->request->getPost('password'));
    $ip = $this->request->getIPAddress();
    $userAgent = $this->request->getUserAgent();

    $maxAttempts = 5;
    $lockoutTime = 3 * 60; // 3 minute lockout time (in seconds)
    $timeWindow = date('Y-m-d H:i:s', strtotime('-15 minutes'));

    // Count recent failed attempts
    $builder = $db->table('login_attempts');
    $attempts = $builder
        ->where('ip_address', $ip)
        ->where('attempt_time >=', $timeWindow)
        ->countAllResults();

    if ($attempts >= $maxAttempts) {
        $lastAttempt = $builder
            ->selectMax('attempt_time')
            ->where('ip_address', $ip)
            ->get()
            ->getRow();

        $lastTime = strtotime($lastAttempt->attempt_time);
        $lockoutExpiry = $lastTime + $lockoutTime;
        $remaining = $lockoutExpiry - time();

        if ($remaining > 0) {
            session()->set('lockout_expiry', $lockoutExpiry);
            return redirect()->to('/login');
        }
    }

    $user = $model->where('ID', $ID)->first();

    if ($user && password_verify($password, $user['password'])) {
        // Success: clear only failed attempts for this IP
        $builder->where('ip_address', $ip)->delete();

        $session->regenerate();
        $session->set([
            'user_id' => $user['id'],
            'ID' => $user['ID'],
            'name' => $user['name'],
            'logged_in' => true,
            'last_activity' => time()
        ]);
         $logModel = new LogModel();
         $logModel->addLog('Login: ' .$user['name'], 'LOGIN');
        return redirect()->to('/dashboard');
    } else {
        // Log the failed attempt
        $builder->insert([
            'ID' => $ID,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'attempt_time' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/login')->with('error', 'Invalid ID or password');
    }
 }

    public function logout()
    {
         $logModel = new LogModel();
         $logModel->addLog('Logout', 'LOGOUT');

        session()->destroy();
        return redirect()->to('/login');
    }

    // Method to clear failed login attempts once lockout period expires
    private function clearFailedAttempts()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('login_attempts');

        $ip = $this->request->getIPAddress();

        // Delete records based on the IP address and older than the lockout period
        $timeThreshold = date('Y-m-d H:i:s', strtotime('-1 minute')); // 1 minute ago

        // Delete only records older than the threshold for this IP address
        $builder->where('ip_address', $ip)
                ->where('attempt_time <', $timeThreshold)
                ->delete();
    }
}
