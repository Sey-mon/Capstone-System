@extends('layouts.app')


@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r flex flex-col items-center py-8 px-4">
        <img src="{{ asset('img/user.png') }}" class="rounded-full mb-3" width="64" height="64" alt="User">
        <div class="text-lg font-semibold text-gray-800">{{ Auth::user()->name }}</div>
        <div class="text-xs text-gray-400 mb-6">Parent</div>
        <nav class="w-full flex-1">
            <ul class="space-y-2">
                <li><a href="/dashboard" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-600"><i class="bi bi-house mr-2"></i>Dashboard</a></li>
                <li><a href="/parent/children" class="block px-4 py-2 rounded bg-green-100 text-green-700 font-semibold"><i class="bi bi-people mr-2"></i>Children</a></li>
                <li><a href="/parent/assessments" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-600"><i class="bi bi-clipboard-data mr-2"></i>Child Assessments</a></li>
                <li><a href="/parent/meal-plans" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-600"><i class="bi bi-egg-fried mr-2"></i>Meal Plans</a></li>
                <li><a href="/parent/profile" class="block px-4 py-2 rounded hover:bg-gray-100 text-gray-600"><i class="bi bi-person mr-2"></i>Profile</a></li>
            </ul>
        </nav>
        <a href="/logout" class="mt-8 w-full text-center py-2 rounded bg-red-50 text-red-500 hover:bg-red-100 transition">Logout</a>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 px-8 py-10">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">My Children</h1>
                    <p class="text-sm text-gray-400">View all your registered children.</p>
                </div>
                <a href="/parent/children/bind" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700 transition"><i class="bi bi-plus-lg mr-2"></i>Bind a Child</a>
            </div>

            <div class="space-y-6">
                @foreach($children as $child)
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-6 flex items-center justify-between shadow-sm hover:shadow-md transition">
                    <div>
                        <div class="text-lg font-semibold text-gray-700 mb-1">{{ $child->name }}</div>
                        <div class="text-sm text-gray-500 mb-1">Age: <span class="text-gray-700">{{ $child->age }}</span></div>
                        <div class="text-sm text-gray-500 mb-1">Nutritionist: <span class="text-gray-700">{{ $child->nutritionist }}</span></div>
                        <div class="text-sm text-gray-500">Assessments: <span class="text-gray-700">{{ $child->assessments_count }}</span></div>
                    </div>
                    <a href="/parent/children/{{ $child->id }}" class="inline-flex items-center px-4 py-2 bg-white border border-green-600 text-green-600 rounded hover:bg-green-50 transition"><i class="bi bi-info-circle mr-2"></i>View Details</a>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</div>
@endsection
