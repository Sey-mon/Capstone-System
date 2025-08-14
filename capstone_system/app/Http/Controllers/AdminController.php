<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Assessment;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\ItemCategory;
use App\Models\Barangay;
use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_patients' => Patient::count(),
            'total_assessments' => Assessment::count(),
            'total_inventory_items' => InventoryItem::count(),
            'recent_transactions' => InventoryTransaction::with(['user', 'inventoryItem'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_audit_logs' => AuditLog::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Show users management
     */
    public function users()
    {
        $users = User::with('role')->paginate(15);
        $roles = Role::all();
        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Show patients management
     */
    public function patients()
    {
        $patients = Patient::with(['barangay'])->paginate(15);
        return view('admin.patients', compact('patients'));
    }

    /**
     * Show assessments management
     */
    public function assessments()
    {
        $assessments = Assessment::with(['patient.barangay'])->paginate(15);
        return view('admin.assessments', compact('assessments'));
    }

    /**
     * Show inventory management
     */
    public function inventory()
    {
        $items = InventoryItem::with(['category', 'inventoryTransactions'])
            ->paginate(15);
        $categories = ItemCategory::all();
        return view('admin.inventory', compact('items', 'categories'));
    }

    /**
     * Store a new inventory item
     */
    public function storeInventoryItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,category_id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            $item = InventoryItem::create([
                'item_name' => $request->item_name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'quantity' => $request->quantity,
                'expiry_date' => $request->expiry_date,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'inventory_items',
                'record_id' => $item->item_id,
                'old_values' => null,
                'new_values' => json_encode($item->toArray()),
                'description' => "Created new inventory item: {$item->item_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item added successfully!',
                'item' => $item->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add inventory item. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an inventory item
     */
    public function updateInventoryItem(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,category_id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            $item = InventoryItem::findOrFail($id);
            $oldValues = $item->toArray();

            $item->update([
                'item_name' => $request->item_name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'quantity' => $request->quantity,
                'expiry_date' => $request->expiry_date,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'inventory_items',
                'record_id' => $item->item_id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($item->toArray()),
                'description' => "Updated inventory item: {$item->item_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item updated successfully!',
                'item' => $item->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory item. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete an inventory item
     */
    public function deleteInventoryItem($id)
    {
        try {
            DB::beginTransaction();

            $item = InventoryItem::findOrFail($id);
            $itemName = $item->item_name;

            // Check if item has transactions
            if ($item->inventoryTransactions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete item with existing transactions. Archive it instead.'
                ], 422);
            }

            // Log the activity before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'inventory_items',
                'record_id' => $item->item_id,
                'old_values' => json_encode($item->toArray()),
                'new_values' => null,
                'description' => "Deleted inventory item: {$itemName}",
            ]);

            $item->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory item. Please try again.'
            ], 500);
        }
    }

    /**
     * Get inventory item details for editing
     */
    public function getInventoryItem($id)
    {
        try {
            $item = InventoryItem::with('category')->findOrFail($id);
            return response()->json([
                'success' => true,
                'item' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.'
            ], 404);
        }
    }

    /**
     * Show reports
     */
    public function reports()
    {
        $reports = [
            'monthly_assessments' => Assessment::whereMonth('created_at', now()->month)->count(),
            'low_stock_items' => InventoryItem::where('quantity', '<', 10)->count(),
            'active_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('admin.reports', compact('reports'));
    }

    /**
     * Show system management page
     */
    public function systemManagement()
    {
        $categories = ItemCategory::withCount('inventoryItems')->paginate(10, ['*'], 'categories_page');
        $barangays = Barangay::withCount('patients')->paginate(10, ['*'], 'barangays_page');
        
        return view('admin.system-management', compact('categories', 'barangays'));
    }

    /**
     * Store a new item category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:item_categories,category_name',
        ]);

        try {
            DB::beginTransaction();

            $category = ItemCategory::create([
                'category_name' => $request->category_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'item_categories',
                'record_id' => $category->category_id,
                'old_values' => null,
                'new_values' => json_encode($category->toArray()),
                'description' => "Created new item category: {$category->category_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category added successfully!',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add category. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an item category
     */
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:item_categories,category_name,' . $id . ',category_id',
        ]);

        try {
            DB::beginTransaction();

            $category = ItemCategory::findOrFail($id);
            $oldValues = $category->toArray();

            $category->update([
                'category_name' => $request->category_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'item_categories',
                'record_id' => $category->category_id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($category->toArray()),
                'description' => "Updated item category: {$category->category_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete an item category
     */
    public function deleteCategory($id)
    {
        try {
            DB::beginTransaction();

            $category = ItemCategory::findOrFail($id);
            $categoryName = $category->category_name;

            // Check if category has inventory items
            if ($category->inventoryItems()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing inventory items.'
                ], 422);
            }

            // Log the activity before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'item_categories',
                'record_id' => $category->category_id,
                'old_values' => json_encode($category->toArray()),
                'new_values' => null,
                'description' => "Deleted item category: {$categoryName}",
            ]);

            $category->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a new barangay
     */
    public function storeBarangay(Request $request)
    {
        $request->validate([
            'barangay_name' => 'required|string|max:255|unique:barangays,barangay_name',
        ]);

        try {
            DB::beginTransaction();

            $barangay = Barangay::create([
                'barangay_name' => $request->barangay_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'barangays',
                'record_id' => $barangay->barangay_id,
                'old_values' => null,
                'new_values' => json_encode($barangay->toArray()),
                'description' => "Created new barangay: {$barangay->barangay_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barangay added successfully!',
                'barangay' => $barangay
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add barangay. Please try again.'
            ], 500);
        }
    }

    /**
     * Update a barangay
     */
    public function updateBarangay(Request $request, $id)
    {
        $request->validate([
            'barangay_name' => 'required|string|max:255|unique:barangays,barangay_name,' . $id . ',barangay_id',
        ]);

        try {
            DB::beginTransaction();

            $barangay = Barangay::findOrFail($id);
            $oldValues = $barangay->toArray();

            $barangay->update([
                'barangay_name' => $request->barangay_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'barangays',
                'record_id' => $barangay->barangay_id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($barangay->toArray()),
                'description' => "Updated barangay: {$barangay->barangay_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barangay updated successfully!',
                'barangay' => $barangay
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update barangay. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a barangay
     */
    public function deleteBarangay($id)
    {
        try {
            DB::beginTransaction();

            $barangay = Barangay::findOrFail($id);
            $barangayName = $barangay->barangay_name;

            // Check if barangay has patients
            if ($barangay->patients()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete barangay with existing patients.'
                ], 422);
            }

            // Log the activity before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'barangays',
                'record_id' => $barangay->barangay_id,
                'old_values' => json_encode($barangay->toArray()),
                'new_values' => null,
                'description' => "Deleted barangay: {$barangayName}",
            ]);

            $barangay->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barangay deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete barangay. Please try again.'
            ], 500);
        }
    }

    /**
     * Get category details for editing
     */
    public function getCategory($id)
    {
        try {
            $category = ItemCategory::findOrFail($id);
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }
    }

    /**
     * Get barangay details for editing
     */
    public function getBarangay($id)
    {
        try {
            $barangay = Barangay::findOrFail($id);
            return response()->json([
                'success' => true,
                'barangay' => $barangay
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barangay not found.'
            ], 404);
        }
    }

    // ========== USER MANAGEMENT CRUD METHODS ==========

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,role_id',
            'contact_number' => 'nullable|string|max:15',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'contact_number' => $request->contact_number,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Created new user: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user
     */
    public function getUser($id)
    {
        try {
            $user = User::with('role')->findOrFail($id);
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')
            ],
            'role_id' => 'required|exists:roles,role_id',
            'contact_number' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $user->toArray();

            $updateData = [
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'contact_number' => $request->contact_number,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Updated user: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            // Check if user has associated records
            $hasPatients = $user->patientsAsParent()->exists() || $user->patientsAsNutritionist()->exists();
            $hasAssessments = $user->assessments()->exists();
            $hasTransactions = $user->inventoryTransactions()->exists();

            if ($hasPatients || $hasAssessments || $hasTransactions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user. User has associated records (patients, assessments, or transactions).'
                ], 400);
            }

            DB::beginTransaction();

            $userName = "{$user->first_name} {$user->last_name}";

            // Log the action before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Deleted user: {$userName}",
            ]);

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }
}
