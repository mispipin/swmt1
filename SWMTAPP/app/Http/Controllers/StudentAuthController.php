<?php

namespace App\Http\Controllers;

use App\Models\TestRegistration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class StudentAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        return view('student.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        return view('student.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'student',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Akun siswa berhasil dibuat.');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'student',
        ], $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => 'Email atau password siswa tidak valid.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Login siswa berhasil.');
    }

    public function dashboard(): View|RedirectResponse
    {
        $student = Auth::user();

        if (!$student || $student->role !== 'student') {
            Auth::logout();

            return redirect()->route('student.login');
        }

        $history = TestRegistration::query()
            ->where('user_id', $student->id)
            ->whereNotNull('total_poin')
            ->latest('tested_at')
            ->latest('created_at')
            ->get();

        $inProgressRegistration = TestRegistration::query()
            ->where('user_id', $student->id)
            ->whereNull('total_poin')
            ->latest('created_at')
            ->first();

        return view('student.dashboard', [
            'history' => $history,
            'inProgressRegistration' => $inProgressRegistration,
        ]);
    }

    public function showWithCodeForm(): View|RedirectResponse
    {
        $student = Auth::user();

        if (!$student || $student->role !== 'student') {
            Auth::logout();

            return redirect()->route('student.login');
        }

        return view('student.enter-class-code');
    }

    public function startWithCode(Request $request): RedirectResponse
    {
        $student = Auth::user();

        if (!$student || $student->role !== 'student') {
            Auth::logout();

            return redirect()->route('student.login');
        }

        $inProgressRegistration = TestRegistration::query()
            ->where('user_id', $student->id)
            ->whereNull('total_poin')
            ->latest('created_at')
            ->first();

        if ($inProgressRegistration) {
            return redirect()
                ->route('test.start', $inProgressRegistration)
                ->with('success', 'Tes sebelumnya belum selesai. Silakan lanjutkan tes terakhir.');
        }

        $validated = $request->validate([
            'class_code' => ['required', 'string', 'max:20'],
        ]);

        $request->session()->put('registration_mode', 'with_code');
        $request->session()->put('pending_class_code', strtoupper(trim($validated['class_code'])));

        return redirect()->route('register.test');
    }

    public function startIndependent(Request $request): RedirectResponse
    {
        $student = Auth::user();

        if (!$student || $student->role !== 'student') {
            Auth::logout();

            return redirect()->route('student.login');
        }

        $inProgressRegistration = TestRegistration::query()
            ->where('user_id', $student->id)
            ->whereNull('total_poin')
            ->latest('created_at')
            ->first();

        if ($inProgressRegistration) {
            return redirect()
                ->route('test.start', $inProgressRegistration)
                ->with('success', 'Tes sebelumnya belum selesai. Silakan lanjutkan tes terakhir.');
        }

        $request->session()->put('registration_mode', 'independent');
        $request->session()->forget('pending_class_code');

        return redirect()->route('register.test');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        $request = request();
        $request->session()->put('google_login_role', 'student');

        return Socialite::driver('google')
            ->scopes(['profile', 'email'])
            ->redirectUrl(config('services.google.redirect'))
            ->redirect();
    }
}