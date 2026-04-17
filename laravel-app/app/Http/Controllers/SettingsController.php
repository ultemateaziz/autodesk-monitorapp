<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $departments = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];
        $roles = ['admin' => 'Master Admin', 'team_leader' => 'Team Leader'];

        $users = [];
        if (auth()->check() && auth()->user()->role === 'admin') {
            $users = User::orderBy('name')->get();
        }

        $settings      = self::getAllSettings();
        $emailSettings = self::getEmailSettings();

        return view('settings', compact('departments', 'roles', 'users', 'settings', 'emailSettings'));
    }

    public function saveWorkingHours(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'work_start' => 'required|date_format:H:i',
            'work_end'   => 'required|date_format:H:i',
        ]);

        $settings = self::getAllSettings();
        $settings['work_start'] = $request->work_start;
        $settings['work_end']   = $request->work_end;

        file_put_contents(
            storage_path('app/archlam_settings.json'),
            json_encode($settings, JSON_PRETTY_PRINT)
        );

        return redirect()->back()->with('success', 'Working hours saved: ' . $request->work_start . ' – ' . $request->work_end);
    }

    public function saveIdleThreshold(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'idle_threshold_minutes' => 'required|integer|min:5|max:480',
        ]);

        $settings = self::getAllSettings();
        $settings['idle_threshold_minutes'] = (int) $request->idle_threshold_minutes;

        file_put_contents(
            storage_path('app/archlam_settings.json'),
            json_encode($settings, JSON_PRETTY_PRINT)
        );

        return redirect()->back()->with('idle_success', 'Idle threshold saved: ' . $request->idle_threshold_minutes . ' minutes.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password you entered is incorrect.'])
                ->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->back()->with('password_success', 'Password changed successfully.');
    }

    public function saveEmailSettings(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'mail_host'         => 'required|string|max:255',
            'mail_port'         => 'required|integer|min:1|max:65535',
            'mail_username'     => 'required|email|max:255',
            'mail_password'     => 'required|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name'    => 'required|string|max:255',
            'hr_email'          => 'required|email|max:255',
        ]);

        // Port 465 = SSL, everything else = TLS/STARTTLS
        $scheme = (int)$request->mail_port === 465 ? 'ssl' : 'tls';

        $values = [
            'MAIL_MAILER'       => 'smtp',
            'MAIL_SCHEME'       => $scheme,
            'MAIL_HOST'         => $request->mail_host,
            'MAIL_PORT'         => $request->mail_port,
            'MAIL_USERNAME'     => $request->mail_username,
            'MAIL_PASSWORD'     => $request->mail_password,
            'MAIL_FROM_ADDRESS' => '"' . $request->mail_from_address . '"',
            'MAIL_FROM_NAME'    => '"' . $request->mail_from_name . '"',
            'HR_EMAIL'          => $request->hr_email,
        ];

        $this->writeEnvValues($values);

        // Save the team leader notification toggle to settings JSON
        $settings = self::getAllSettings();
        $settings['notify_team_leaders'] = $request->has('notify_team_leaders');
        file_put_contents(
            storage_path('app/archlam_settings.json'),
            json_encode($settings, JSON_PRETTY_PRINT)
        );

        // Clear config cache so new values take effect immediately
        Artisan::call('config:clear');

        return redirect()->back()->with('email_success', 'Email settings saved. You can now send a test email to verify.');
    }

    public function testEmail(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $recipient = auth()->user()->email;
        $appName   = config('app.name', 'ASCLAM');

        try {
            Mail::raw(
                "This is a test email from {$appName}.\n\n" .
                "Your SMTP email configuration is working correctly.\n\n" .
                "SMTP Host: " . config('mail.mailers.smtp.host') . "\n" .
                "SMTP Port: " . config('mail.mailers.smtp.port') . "\n" .
                "Sent to:   {$recipient}\n",
                function ($message) use ($recipient, $appName) {
                    $message->to($recipient)
                            ->subject("[{$appName}] Test Email — SMTP Configuration Verified");
                }
            );

            return redirect()->back()->with('email_test_success', "Test email sent to {$recipient}. Please check your inbox.");
        } catch (\Exception $e) {
            return redirect()->back()->with('email_test_error', 'Failed to send: ' . $e->getMessage());
        }
    }

    /**
     * Safely write key=value pairs into the .env file.
     * Updates existing keys in-place and appends any new ones.
     */
    private function writeEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($values as $key => $value) {
            // If value contains spaces and is not already quoted, wrap it
            $writeValue = $value;

            $pattern     = "/^{$key}=.*/m";
            $replacement = "{$key}={$writeValue}";

            if (preg_match($pattern, $content)) {
                // Replace existing key
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                // Append new key at the end
                $content .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Read current SMTP values from .env to pre-fill the form.
     */
    public static function getEmailSettings(): array
    {
        $settings = self::getAllSettings();

        return [
            'mail_host'            => env('MAIL_HOST', 'smtp.gmail.com'),
            'mail_port'            => env('MAIL_PORT', 587),
            'mail_username'        => env('MAIL_USERNAME', ''),
            'mail_password'        => env('MAIL_PASSWORD', ''),
            'mail_from_address'    => str_replace('"', '', env('MAIL_FROM_ADDRESS', '')),
            'mail_from_name'       => str_replace('"', '', env('MAIL_FROM_NAME', 'ASCLAM')),
            'hr_email'             => env('HR_EMAIL', ''),
            'notify_team_leaders'  => $settings['notify_team_leaders'] ?? false,
        ];
    }

    /**
     * Public API: returns the idle threshold so the monitor client can read it.
     */
    public function idleThresholdApi()
    {
        $settings = self::getAllSettings();
        return response()->json([
            'idle_threshold_minutes' => $settings['idle_threshold_minutes'] ?? 60,
            'idle_threshold_ms'      => (($settings['idle_threshold_minutes'] ?? 60) * 60000),
        ]);
    }

    /**
     * Read all settings from storage. Returns defaults if not set.
     */
    public static function getAllSettings(): array
    {
        $path = storage_path('app/archlam_settings.json');

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) return $data;
        }

        return [
            'work_start'             => '08:00',
            'work_end'               => '18:00',
            'idle_threshold_minutes' => 60,
        ];
    }

    /**
     * Backward-compatible alias used by other controllers.
     */
    public static function getWorkingHours(): array
    {
        return self::getAllSettings();
    }
}
