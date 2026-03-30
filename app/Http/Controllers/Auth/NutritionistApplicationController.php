<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class NutritionistApplicationController extends Controller
{
    /**
     * Show the nutritionist application form.
     */
    public function showApplicationForm()
    {
        return view('auth.apply-nutritionist');
    }

    /**
     * Handle the nutritionist application submission.
     */
    public function submitApplication(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['required', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'qualifications' => ['required', 'string', 'max:2000'],
            'experience' => ['required', 'string', 'max:2000'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get nutritionist role ID
            $nutritionistRole = Role::where('role_name', 'Nutritionist')->first();
            if (!$nutritionistRole) {
                return back()
                    ->withErrors(['error' => 'Nutritionist role not found. Please contact administrator.'])
                    ->withInput();
            }

            // Create the nutritionist user account
            $user = User::create([
                'role_id' => $nutritionistRole->role_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'sex' => $request->gender,
                'email' => $request->email,
                'password' => $request->password, // Will be hashed automatically
                'contact_number' => $request->contact_number,
                'years_experience' => $request->years_experience ?? 0,
                'qualifications' => $request->qualifications,
                'professional_experience' => $request->experience,
                'verification_status' => 'pending',
                'account_status' => 'pending',
                'is_active' => false, // Account will be activated upon approval
            ]);

            // Store user ID in session for success page
            session(['application_id' => $user->user_id]);

            // Optionally send notification email to admin
            // Mail::to('admin@example.com')->send(new NewNutritionistApplication($user));

            return redirect()->route('nutritionist.application.success');

        } catch (\Exception $e) {
            // Log the error
            Log::error('Nutritionist application error: ' . $e->getMessage());

            return back()
                ->withErrors(['error' => 'An error occurred while submitting your application. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show the application success page.
     */
    public function showSuccessPage()
    {
        return view('auth.registration-success');
    }

    /**
     * Get pending nutritionist application by ID (for admin review)
     */
    public function show($id)
    {
        $user = User::with(['role', 'verifier'])
            ->where('user_id', $id)
            ->where('account_status', 'pending')
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Nutritionist');
            })
            ->firstOrFail();

        return view('admin.nutritionist-applications.show', compact('user'));
    }

    /**
     * List all pending nutritionist applications (for admin)
     */
    public function index()
    {
        $applications = User::with(['role', 'verifier'])
            ->where('account_status', 'pending')
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Nutritionist');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.nutritionist-applications.index', compact('applications'));
    }

    /**
     * Approve nutritionist application
     */
    public function approve(Request $request, $id)
    {
        $user = User::where('user_id', $id)
            ->where('account_status', 'pending')
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Nutritionist');
            })
            ->firstOrFail();
        
        if (!$user->isAccountPending()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        try {
            // Approve the application
            $user->approveVerification(Auth::user());

            // Send approval email
            // Mail::to($user->email)->send(new ApplicationApproved($user));

            return back()->with('success', 'Nutritionist application approved successfully.');

        } catch (\Exception $e) {
            Log::error('Application approval error: ' . $e->getMessage());
            return back()->with('error', 'Error approving application.');
        }
    }

    /**
     * Reject nutritionist application
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $user = User::where('user_id', $id)
            ->where('account_status', 'pending')
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Nutritionist');
            })
            ->firstOrFail();
        
        if (!$user->isAccountPending()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        try {
            // Reject the application
            $user->rejectVerification($request->rejection_reason, Auth::user());

            // Send rejection email
            // Mail::to($user->email)->send(new ApplicationRejected($user));

            return back()->with('success', 'Nutritionist application rejected.');

        } catch (\Exception $e) {
            Log::error('Application rejection error: ' . $e->getMessage());
            return back()->with('error', 'Error rejecting application.');
        }
    }
}
