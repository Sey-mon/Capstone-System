<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    /**
     * Show parent dashboard
     */
    public function dashboard()
    {
        $parent = Auth::user();
        
        $stats = [
            'my_children' => Patient::where('parent_id', $parent->user_id)->count(),
            'total_assessments' => Assessment::whereHas('patient', function($query) use ($parent) {
                $query->where('parent_id', $parent->user_id);
            })->count(),
            'recent_assessments' => Assessment::whereHas('patient', function($query) use ($parent) {
                $query->where('parent_id', $parent->user_id);
            })->count(),
            'my_children_list' => Patient::where('parent_id', $parent->user_id)
                ->with(['nutritionist', 'assessments'])
                ->get(),
            'recent_assessments_list' => Assessment::whereHas('patient', function($query) use ($parent) {
                $query->where('parent_id', $parent->user_id);
            })
                ->with(['patient', 'nutritionist'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('parent.dashboard', compact('stats'));
    }

    /**
     * Show children (patients) of this parent
     */
    public function children()
    {
        $parent = Auth::user();
        $children = Patient::where('parent_id', $parent->user_id)
            ->with(['nutritionist', 'assessments'])
            ->paginate(15);

        return view('parent.children', compact('children'));
    }

    /**
     * Show assessments for parent's children
     */
    public function assessments()
    {
        $parent = Auth::user();
        $assessments = Assessment::whereHas('patient', function($query) use ($parent) {
            $query->where('parent_id', $parent->user_id);
        })
            ->with(['patient', 'nutritionist'])
            ->paginate(15);

        return view('parent.assessments', compact('assessments'));
    }

    /**
     * Show parent profile
     */
    public function profile()
    {
        $parent = Auth::user();
        return view('parent.profile', compact('parent'));
    }
}
