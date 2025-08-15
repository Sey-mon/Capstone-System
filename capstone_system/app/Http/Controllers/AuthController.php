<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        // First check if user exists and is not soft deleted
        $user = User::where('email', $credentials['email'])->whereNull('deleted_at')->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput();
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is pending approval. Please wait for admin activation.',
                ])->withInput();
            }

            $request->session()->regenerate();
            
            // Log successful login
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'login',
                'description' => 'User logged in successfully',
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

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect to appropriate dashboard based on user role
     */
    private function redirectToDashboard()
    {
        $user = Auth::user();
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
            'birth_date' => 'required|date|before:today|after:' . now()->subYears(120)->format('Y-m-d'),
            'sex' => 'required|in:Male,Female,Other',
            'address' => 'required|string|max:1000',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,user_id,deleted_at,NULL',
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
                'regex:/^09\d{2}-\d{3}-\d{4}$/' // Philippine phone format
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
            'child_first_name.regex' => 'Child\'s first name can only contain letters, spaces, hyphens, and periods.',
            'child_last_name.regex' => 'Child\'s last name can only contain letters, spaces, hyphens, and periods.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).',
            'contact_number.regex' => 'Contact number must be in the format 09XX-XXX-XXXX.',
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

            // Create the parent user
            $user = User::create([
                'role_id' => $parentRole->role_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'birth_date' => $request->birth_date,
                'sex' => $request->sex,
                'address' => $request->address,
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

            DB::commit();

            // Don't auto-login the user - let them login manually
            // Auth::login($user);

            return redirect()->route('registration.success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])->withInput();
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
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,user_id,deleted_at,NULL',
            'contact_number' => 'required|string|max:20',
            'license_number' => 'required|string|max:255',
            'qualifications' => 'required|string|max:1000',
            'experience' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Store application in session or database for admin review
        // For now, we'll create a pending nutritionist account that needs admin activation
        
        // Create a temporary password
        $tempPassword = 'temp_' . substr(md5(time() . $request->email), 0, 8);
        
        // Get Nutritionist role ID
        $nutritionistRole = Role::where('role_name', 'Nutritionist')->first();
        if (!$nutritionistRole) {
            return back()->withErrors(['error' => 'Nutritionist role not found in system.'])->withInput();
        }

        // Create the nutritionist user (inactive initially)
        $user = User::create([
            'role_id' => $nutritionistRole->role_id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'contact_number' => $request->contact_number,
            'is_active' => false, // This will need to be added to users table
        ]);

        // Log the application
        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'application_submitted',
            'description' => "Nutritionist application submitted. License: {$request->license_number}, Qualifications: {$request->qualifications}, Experience: {$request->experience}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('login')->with('success', 'Your application has been submitted successfully! You will receive an email notification once your application is reviewed and approved by our admin team.');
    }
}
