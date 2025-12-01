<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Handle sending password reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        // LAYER 1: Honeypot Protection - Check if bot filled hidden field
        if ($request->filled('website')) {
            // Bot detected, silently reject
            return back()->withErrors([
                'email' => 'Invalid submission attempt.',
            ])->withInput($request->except('email'));
        }

        // LAYER 2: Google reCAPTCHA Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('email'));
        }

        // LAYER 3: Verify reCAPTCHA with Google API
        $recaptchaSecret = config('services.recaptcha.secret_key');
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret);
        $resp = $recaptcha->verify($request->input('g-recaptcha-response'), $request->ip());

        if (!$resp->isSuccess()) {
            return back()->withErrors([
                'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
            ])->withInput($request->except('email'));
        }

        // Find user by email
        $user = User::findByEmail($request->email);
        if (!$user) {
            return back()->withErrors(['email' => 'No account found with that email.']);
        }
        
        // Generate token and send email
        $token = app('auth.password.broker')->createToken($user);
        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));
        Mail::to($user->email)->queue(new \App\Mail\PasswordResetMail($user, $resetUrl));
        return back()->with('success', 'A password reset link has been sent to your email.');
    }

    /**
     * Handle sending contact admin message
     */
    public function sendContactAdmin(Request $request)
    {
        // LAYER 1: Honeypot Protection - Check if bot filled hidden field
        if ($request->filled('website')) {
            // Bot detected, silently reject
            return back()->withErrors([
                'email' => 'Invalid submission attempt.',
            ])->withInput($request->except('message'));
        }

        // LAYER 2: Google reCAPTCHA Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'message' => 'required|string|max:1000',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('message'));
        }

        // LAYER 3: Verify reCAPTCHA with Google API
        $recaptchaSecret = config('services.recaptcha.secret_key');
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret);
        $resp = $recaptcha->verify($request->input('g-recaptcha-response'), $request->ip());

        if (!$resp->isSuccess()) {
            return back()->withErrors([
                'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
            ])->withInput($request->except('message'));
        }

        // Send email to admin (replace with actual admin email)
        $adminEmail = config('mail.from.address', 'admin@example.com');
        Mail::raw('From: ' . $request->email . "\n\nMessage:\n" . $request->message, function ($message) use ($request, $adminEmail) {
            $message->to($adminEmail)
                ->subject('Contact Admin Message');
        });
        return back()->with('success', 'Your message has been sent to the admin.');
    }
    /**
     * Show the login form (Public/Parent Login)
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        return view('auth.login');
    }

    /**
     * Show staff login page (Admin, Nutritionist, Health Workers, BHW)
     */
    public function showStaffLogin(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        return view('auth.staff-login');
    }

    /**
     * Handle staff login request (Admin, Nutritionist, Health Workers, BHW)
     */
    public function staffLogin(Request $request)
    {
        // LAYER 1: Honeypot Protection - Check if bot filled hidden field
        if ($request->filled('website')) {
            // Bot detected, silently reject
            return back()->withErrors([
                'email' => 'Invalid login attempt.',
            ])->withInput($request->except('password'));
        }

        // LAYER 2: Google reCAPTCHA Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('password'));
        }

        // Verify reCAPTCHA with Google
        $recaptchaSecret = config('services.recaptcha.secret_key');
        if ($recaptchaSecret && $recaptchaSecret !== '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe') {
            $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret);
            $resp = $recaptcha->verify($request->input('g-recaptcha-response'), $request->ip());
            
            if (!$resp->isSuccess()) {
                return back()->withErrors([
                    'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
                ])->withInput($request->except('password'));
            }
        }

        $credentials = $request->only('email', 'password');

        // First check if user exists using encryption-aware method
        $user = User::findByEmail($credentials['email']);
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput();
        }

        // Verify staff role (Admin, Nutritionist, Health Worker, BHW)
        $roleName = $user->role->role_name ?? null;
        $allowedRoles = ['Admin', 'Nutritionist', 'Health Worker', 'BHW'];
        
        if (!in_array($roleName, $allowedRoles)) {
            return back()->withErrors([
                'email' => 'You do not have staff portal access.',
            ])->withInput();
        }

        // Check password
        if (Hash::check($credentials['password'], $user->password)) {
            // Check if user is active
            if (!$user->is_active) {
                return back()->withErrors([
                    'email' => 'Your account is pending approval. Please wait for admin activation.',
                ])->withInput();
            }

            // Log the user in manually
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            
            // Store login portal for logout redirect
            $request->session()->put('login_portal', 'staff');
            
            // Log successful login
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'staff_login',
                'description' => 'Staff member logged in successfully',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->redirectToDashboard();
        }

        return back()->withErrors([
            'email' => 'Invalid staff credentials.',
        ])->withInput();
    }

    /**
     * Handle login request (Public/Parent)
     */
    public function login(Request $request)
    {
        // LAYER 1: Honeypot Protection - Check if bot filled hidden field
        if ($request->filled('website')) {
            // Bot detected, silently reject
            return back()->withErrors([
                'email' => 'Invalid login attempt.',
            ])->withInput($request->except('password'));
        }

        // LAYER 2: Google reCAPTCHA Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('password'));
        }

        // Verify reCAPTCHA with Google
        $recaptchaSecret = config('services.recaptcha.secret_key');
        if ($recaptchaSecret && $recaptchaSecret !== '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe') {
            $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret);
            $resp = $recaptcha->verify($request->input('g-recaptcha-response'), $request->ip());
            
            if (!$resp->isSuccess()) {
                return back()->withErrors([
                    'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
                ])->withInput($request->except('password'));
            }
        }

        $credentials = $request->only('email', 'password');

        // First check if user exists using encryption-aware method
        $user = User::findByEmail($credentials['email']);
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput();
        }

        // Verify parent role only for public login
        $roleName = $user->role->role_name ?? null;
        if ($roleName !== 'Parent') {
            return back()->withErrors([
                'email' => 'This login is for parents only. Staff members, please use the Staff Portal.',
            ])->withInput();
        }

        // Check password manually since we're using encrypted emails
        if (Hash::check($credentials['password'], $user->password)) {
            // Check if user is active
            if (!$user->is_active) {
                return back()->withErrors([
                    'email' => 'Your account is pending approval. Please wait for admin activation.',
                ])->withInput();
            }

            // Log the user in manually
            Auth::login($user, $request->filled('remember'));

            $request->session()->regenerate();
            
            // Store login portal for logout redirect
            $request->session()->put('login_portal', 'parent');
            
            // Log successful login
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'parent_login',
                'description' => 'Parent logged in successfully',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->redirectToDashboard();
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Get login portal before session is invalidated
        $loginPortal = $request->session()->get('login_portal', 'parent');
        
        // Log logout activity
        if (Auth::check()) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect based on which portal they used to login
        if ($loginPortal === 'staff') {
            return redirect()->route('staff.login')->with('success', 'You have been logged out successfully.');
        }

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect to appropriate dashboard based on user role
     */
    private function redirectToDashboard()
    {
        $user = Auth::user();
        
        // Check if email is verified - redirect to verification gate (paywall style)
        if ($user->email_verified_at === null) {
            return redirect()->route('verification.gate');
        }
        
        $roleName = $user->role->role_name ?? null;

        switch ($roleName) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Nutritionist':
                return redirect()->route('nutritionist.dashboard');
            case 'Parent':
                return redirect()->route('parent.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Invalid user role.']);
        }
    }

    /**
     * Show registration options page
     */
    public function showRegistrationOptions()
    {
        return view('auth.register-options');
    }

    /**
     * Show parent registration form
     */
    public function showParentRegistration()
    {
        return view('auth.register-parent');
    }

    /**
     * Handle parent registration
     */
    public function registerParent(Request $request)
    {
        // First, check if email already exists using our encryption-aware method
        if (User::emailExists($request->email)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['This email address is already registered. Please use a different email address or contact support if you believe this is an error.']
                ]);
            }
            return back()->withErrors([
                'email' => 'This email address is already registered. Please use a different email address or contact support if you believe this is an error.'
            ])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/' // Only letters, spaces, hyphens, periods
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ],
            'suffix' => [
                'nullable',
                'string',
                'in:Jr.,Sr.,II,III,IV,V'
            ],
            'birth_date' => 'required|date|before:today|after:' . now()->subYears(120)->format('Y-m-d'),
            'sex' => 'required|in:Male,Female,Other',
            'house_street' => 'required|string|max:500',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000', // Combined address (auto-generated)
            'email' => 'required|string|email|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/' // Strong password with more special chars
            ],
            'contact_number' => [
                'required',
                'string',
                'regex:/^09\d{9}$/' // Updated to match new format: 09XXXXXXXXX (11 digits, no dashes)
            ],
            'child_first_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ],
            'child_last_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ],
            'child_age_months' => 'nullable|integer|min:0|max:60',
        ], [
            // Custom error messages
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and periods.',
            'middle_name.regex' => 'Middle name can only contain letters, spaces, hyphens, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and periods.',
            'suffix.in' => 'Please select a valid suffix from the dropdown.',
            'house_street.required' => 'House/Street address is required.',
            'house_street.max' => 'House/Street address must not exceed 500 characters.',
            'barangay.required' => 'Please select your barangay.',
            'city.required' => 'City is required.',
            'province.required' => 'Province is required.',
            'child_first_name.regex' => 'Child\'s first name can only contain letters, spaces, hyphens, and periods.',
            'child_last_name.regex' => 'Child\'s last name can only contain letters, spaces, hyphens, and periods.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).',
            'contact_number.regex' => 'Contact number must be an 11-digit Philippine mobile number starting with 09.',
            'birth_date.before' => 'Birth date must be in the past.',
            'birth_date.after' => 'Please enter a valid birth date.',
            'child_age_months.min' => 'Child age must be at least 0 months.',
            'child_age_months.max' => 'Child age must be 60 months (5 years) or less.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get Parent role ID
        $parentRole = Role::where('role_name', 'Parent')->first();
        if (!$parentRole) {
            return back()->withErrors(['error' => 'Parent role not found in system.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Combine address components into complete address for storage
            $addressComponents = array_filter([
                $request->house_street,
                $request->barangay,
                $request->city,
                $request->province
            ]);
            $completeAddress = implode(', ', $addressComponents);

            // Create the parent user (using existing table structure)
            $user = User::create([
                'role_id' => $parentRole->role_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'birth_date' => $request->birth_date,
                'sex' => $request->sex,
                'address' => $completeAddress, // Store combined address for now
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'contact_number' => $request->contact_number,
            ]);

            // If child information is provided, attempt to find and create a pending connection
            if ($request->filled('child_first_name') && $request->filled('child_last_name') && $request->filled('child_age_months')) {
                $this->attemptChildLinking($user, $request);
            }

            // Log the registration
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'registration',
                'description' => 'Parent account registered successfully' . 
                    ($request->filled('child_first_name') ? ' with child linking request' : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Send welcome email (queued)
            try {
                Mail::to($user->email)->queue(new WelcomeEmail($user));
                
                // Log email queued
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'action' => 'welcome_email_queued',
                    'description' => 'Welcome email queued for sending to ' . $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                // Log email failure but don't stop registration
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'action' => 'welcome_email_failed',
                    'description' => 'Failed to queue welcome email: ' . $e->getMessage(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            DB::commit();

            // Log the user in temporarily to send email verification
            Auth::login($user);
            
            // Send email verification notification
            try {
                $user->sendEmailVerificationNotification();
                
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'action' => 'verification_email_sent',
                    'description' => 'Email verification notification sent to ' . $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'action' => 'verification_email_failed',
                    'description' => 'Failed to send verification email: ' . $e->getMessage(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // Log the user out after sending verification email
            // so they can return to login page if needed
            Auth::logout();
            
            // Create secure success message without personal information
            $successMessage = 'Account created successfully! Please check your email to verify your account before logging in.';
            
            // Redirect to login page with success message
            return redirect()->route('login')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if it's a duplicate entry error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'users_email_unique') !== false) {
                return back()->withErrors([
                    'email' => 'This email address is already registered. Please use a different email address or try logging in if you already have an account.'
                ])->withInput();
            }
            
            // For other errors, show a generic message
            return back()->withErrors([
                'error' => 'Registration failed. Please check your information and try again. If the problem persists, please contact support.'
            ])->withInput();
        }
    }

    /**
     * Attempt to link child to parent account
     */
    private function attemptChildLinking($parent, $request)
    {
        // Get age in months directly from the form
        $ageInMonths = (int) $request->child_age_months;
        
        // Search for existing patient with matching information
        // Allow for ±2 months tolerance in age matching
        $potentialMatches = \App\Models\Patient::where('first_name', 'LIKE', '%' . $request->child_first_name . '%')
            ->where('last_name', 'LIKE', '%' . $request->child_last_name . '%')
            ->whereBetween('age_months', [$ageInMonths - 2, $ageInMonths + 2]) // ±2 months tolerance
            ->whereNull('parent_id') // Only unlinked patients
            ->get();

        // Create audit log for linking attempt
        $description = "Parent linking request: Child name: {$request->child_first_name} {$request->child_last_name}, Age: {$ageInMonths} months";
        
        if ($potentialMatches->count() > 0) {
            $description .= ". Found {$potentialMatches->count()} potential match(es) - requires admin verification.";
            
            // Store the potential matches for admin review (you could create a separate table for this)
            foreach ($potentialMatches as $match) {
                AuditLog::create([
                    'user_id' => $parent->user_id,
                    'action' => 'child_link_request',
                    'description' => $description . " Patient ID: {$match->patient_id} (Patient age: {$match->age_months} months)",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } else {
            $description .= ". No matches found - child may need to be registered first.";
            
            AuditLog::create([
                'user_id' => $parent->user_id,
                'action' => 'child_link_request',
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Show nutritionist application form
     */
    public function showNutritionistApplication()
    {
        return view('auth.apply-nutritionist');
    }

    /**
     * Handle nutritionist application
     */
    public function applyNutritionist(Request $request)
    {
        // First, check if email already exists to provide a friendly error message
        $existingUser = User::findByEmail($request->email);
        if ($existingUser) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['This email address is already registered. Please use a different email address or contact support if you believe this is an error.']
                ]);
            }
            return back()->withErrors([
                'email' => 'This email address is already registered. Please use a different email address or contact support if you believe this is an error.'
            ])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,user_id,deleted_at,NULL',
            'contact_number' => 'required|string|max:20',
            'sex' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:255',
            'years_experience' => 'nullable|integer|min:0|max:50',
            // Educational qualifications: allow short entries for real-world cases
            'qualifications' => 'required|string|min:2|max:1000',
            // Professional experience: at least 10 characters, but allow short entries for real-world cases
            'professional_experience' => 'required|string|min:10|max:1000',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'qualifications.min' => 'Please provide at least your school, degree, or certification.',
            'professional_experience.min' => 'Please briefly describe your work experience or position.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Get Nutritionist role ID
        $nutritionistRole = Role::where('role_name', 'Nutritionist')->first();
        if (!$nutritionistRole) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => ['Nutritionist role not found in system.']]);
            }
            return back()->withErrors(['error' => 'Nutritionist role not found in system.'])->withInput();
        }

        try {
            $user = User::create([
                'role_id' => $nutritionistRole->role_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'sex' => $request->sex,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'license_number' => $request->license_number,
                'years_experience' => $request->years_experience,
                'qualifications' => $request->qualifications,
                'professional_experience' => $request->professional_experience,
                'is_active' => false,
            ]);

            // Note: Email will be auto-verified when admin activates the account
            // No need to send verification email here since account requires admin approval first

            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'application_submitted',
                'description' => "Nutritionist application submitted. License: {$request->license_number}, Qualifications: {$request->qualifications}, Experience: {$request->professional_experience}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create success message for staff application
            $successMessage = 'Application submitted successfully! Your account will be reviewed by our admin team. You will receive an email notification once your account is approved and activated.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $successMessage]);
            }
            
            // Redirect to staff login page with success message
            return redirect()->route('staff.login')->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Nutritionist application error: ' . $e->getMessage(), ['exception' => $e]);
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'Duplicate entry') !== false && strpos($errorMsg, 'users_license_number_unique') !== false) {
                $friendlyMsg = 'This license number is already registered. Please use a different license number or contact support if you believe this is an error.';
            } else {
                $friendlyMsg = 'Application submission failed. Error: ' . $errorMsg;
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => [$friendlyMsg]]);
            }
            return back()->withErrors([
                'error' => $friendlyMsg
            ])->withInput();
        }
    }

    /**
     * Show verification gate - paywall style blocking page
     */
    public function showVerificationGate()
    {
        $user = Auth::user();
        
        // If already verified, redirect to dashboard
        if ($user->email_verified_at !== null) {
            return $this->redirectToDashboard();
        }
        
        return view('auth.verification-gate', compact('user'));
    }

    /**
     * Resend verification email and logout user
     */
    public function resendAndLogout(Request $request)
    {
        $user = Auth::user();
        
        try {
            // Send verification email
            $user->sendEmailVerificationNotification();
            
            // Log the action
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'verification_email_resent',
                'description' => 'Verification email resent and user logged out',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Logout user
            Auth::logout();
            
            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('verification.notice')->with('message', 'Verification email sent! Please check your inbox and click the verification link.');
            
        } catch (\Exception $e) {
            // Log failure
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'verification_email_failed',
                'description' => 'Failed to resend verification email: ' . $e->getMessage(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Logout anyway and show error
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('verification.notice')->withErrors([
                'error' => 'Failed to send verification email. Please try again later or contact support.'
            ]);
        }
    }

    /**
     * Resend verification email for any email address (public route)
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Use the User model's findByEmail method to handle encrypted emails
        $user = User::findByEmail($request->email);
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.'
            ]);
        }
        
        // Check if already verified
        if ($user->email_verified_at !== null) {
            return back()->with('info', 'This email address is already verified. You can login now.');
        }

        try {
            // Send verification email
            $user->sendEmailVerificationNotification();
            
            // Log the action
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'verification_email_resent_public',
                'description' => 'Verification email resent via public form for ' . $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->with('success', 'Verification email has been sent! Please check your inbox.');
            
        } catch (\Exception $e) {
            // Log failure
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'verification_email_failed_public',
                'description' => 'Failed to resend verification email via public form: ' . $e->getMessage(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to send verification email. Please try again later or contact support.'
            ]);
        }
    }

    /**
     * Show development verification panel
     */
    public function showDevVerificationPanel()
    {
        // Only allow in development
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }

        $unverifiedUsers = User::whereNull('email_verified_at')->get();
        return view('dev.verification-panel', compact('unverifiedUsers'));
    }

    /**
     * Development-only email verification bypass
     */
    public function devVerifyEmail($email)
    {
        // Only allow in development
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }

        $user = User::findByEmail($email);
        
        if (!$user) {
            return redirect()->route('dev.panel')->withErrors([
                'error' => 'User not found with email: ' . $email
            ]);
        }

        // Mark email as verified
        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
            $user->save();

            // Log the action
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'dev_email_verified',
                'description' => 'Email verified via development bypass for ' . $email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('dev.panel')->with('success', 'Email verified for: ' . $email);
        } else {
            return redirect()->route('dev.panel')->with('info', 'Email already verified for: ' . $email);
        }
    }

    /**
     * Verify email address from verification link
     */
    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        // Get the actual email for verification (handle encrypted emails)
        $emailForVerification = $user->getEmailForVerification();
        
        // Check if the hash matches
        if (! hash_equals((string) $request->route('hash'), sha1($emailForVerification))) {
            return redirect()->route('login')->withErrors(['error' => 'Invalid verification link.']);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('success', 'Your email is already verified. You can now log in.');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            // Log the verification
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'email_verified',
                'description' => 'Email address verified successfully via verification link',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Get decrypted email for display
            $encryptionService = app(\App\Services\DataEncryptionService::class);
            $displayEmail = $encryptionService->isEncrypted($user->email) 
                ? $encryptionService->decryptUserData($user->email) 
                : $user->email;

            // Pass user data to success page via session
            return redirect()->route('verification.success')->with([
                'verified_email' => $displayEmail,
                'user_name' => $user->getFullNameAttribute(),
                'verified_at' => now()->format('F j, Y \a\t g:i A')
            ]);
        }

        return redirect()->route('login')->withErrors(['error' => 'Failed to verify email. Please try again.']);
    }
}
