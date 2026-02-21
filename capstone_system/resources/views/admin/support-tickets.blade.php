@extends('layouts.dashboard')

@section('title', 'Support Tickets')

@section('page-title', 'Support Tickets')
@section('page-subtitle', 'View and manage problem reports submitted by users')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-users.css') }}?v={{ filemtime(public_path('css/admin/admin-users.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin/support-tickets.css') }}?v={{ filemtime(public_path('css/admin/support-tickets.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush
        
@section('navigation')
    @include('partials.admin-navigation')
@endsection

@section('content')
    <!-- Filter Section -->
    <div class="filter-container">
        <div class="filter-header-bar">
            <h3><i class="fas fa-filter"></i> Filters & Search</h3>
            <button type="button" class="btn-clear-all" onclick="window.location.href='{{ route('admin.support-tickets') }}?filter={{ request('filter', 'all') }}'">
                <i class="fas fa-times"></i> Clear All
            </button>
        </div>
        
        <div class="filter-content">
            <form method="GET" action="{{ route('admin.support-tickets') }}" onsubmit="event.preventDefault();" class="filters-form">
                <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">
                
                <div class="filter-grid" style="grid-template-columns: 1.5fr 1fr 1fr 1fr 1.2fr;">
                    <!-- Search Input -->
                    <div class="filter-field">
                        <label>Search Ticket</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Search by ticket #, email, subject..." 
                                class="form-control search-input"
                            >
                        </div>
                    </div>
                    
                    <!-- Priority Filter -->
                    <div class="filter-field">
                        <label>Priority</label>
                        <select name="priority" class="form-control filter-select">
                            <option value="" {{ request('priority') == '' ? 'selected' : '' }}>All Priorities</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>🔥 Urgent</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>ℹ️ Normal</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="filter-field">
                        <label>Status</label>
                        <select name="status" class="form-control filter-select">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Statuses</option>
                            <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>📨 Unread</option>
                            <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>📖 Read</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>✅ Resolved</option>
                        </select>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="filter-field">
                        <label>Category</label>
                        <select name="category" class="form-control filter-select">
                            <option value="" {{ request('category') == '' ? 'selected' : '' }}>All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $cat)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date Range -->
                    <div class="filter-field">
                        <label>Date Range</label>
                        <div class="filter-date-range">
                            <input 
                                type="date" 
                                name="date_from" 
                                value="{{ request('date_from') }}"
                                placeholder="From"
                                class="filter-date-input"
                            >
                            <span class="filter-date-separator">-</span>
                            <input 
                                type="date" 
                                name="date_to" 
                                value="{{ request('date_to') }}"
                                placeholder="To"
                                class="filter-date-input"
                            >
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Tabs -->
    @if(request('filter') != 'archived')
    <div class="filter-tabs">
        <button class="filter-tab {{ request('filter') == '' || request('filter') == 'all' ? 'active' : '' }}" onclick="filterTickets('all')">
            <i class="fas fa-list"></i> All Active ({{ $stats['total'] }})
        </button>
        <button class="filter-tab {{ request('filter') == 'unread' ? 'active' : '' }}" onclick="filterTickets('unread')">
            <i class="fas fa-exclamation-circle"></i> Unread ({{ $stats['unread'] }})
        </button>
        <button class="filter-tab {{ request('filter') == 'urgent' ? 'active' : '' }}" onclick="filterTickets('urgent')">
            <i class="fas fa-fire"></i> Urgent ({{ $stats['urgent'] }})
        </button>
        <button class="filter-tab {{ request('filter') == 'resolved' ? 'active' : '' }}" onclick="filterTickets('resolved')">
            <i class="fas fa-check-circle"></i> Resolved ({{ $stats['resolved'] }})
        </button>
    </div>
    @endif

    <!-- Content Card -->
    <div class="content-card">
        <div class="card-header-modern">
            <div class="header-title-section">
                <div class="title-with-icon">
                    <i class="fas fa-{{ request('filter') == 'archived' ? 'archive' : 'ticket-alt' }}"></i>
                    <h3 class="card-title-modern">{{ request('filter') == 'archived' ? 'Archived Tickets' : 'Support Tickets' }}</h3>
                </div>
                <p class="card-subtitle">{{ request('filter') == 'archived' ? 'Historical ticket records for reference' : 'View and manage problem reports submitted by users' }}</p>
            </div>
            <div class="header-actions">
                @if(request('filter') != 'archived')
                    <button class="btn-count" onclick="filterTickets('active')" title="Show all non-archived tickets">
                        <i class="fas fa-ticket-alt"></i> {{ $stats['total'] }} active
                    </button>
                    @if($stats['unread'] > 0)
                    <button class="btn-count" style="background: #ef4444; color: white; cursor: pointer;" onclick="filterTickets('unread')" title="Show unread tickets">
                        <i class="fas fa-exclamation-circle"></i> {{ $stats['unread'] }} unread
                    </button>
                    @endif
                @endif
                
                <button class="btn-archive-toggle {{ request('filter') == 'archived' ? 'active' : '' }}" onclick="filterTickets('{{ request('filter') == 'archived' ? 'all' : 'archived' }}')">
                    @if(request('filter') == 'archived')
                        <i class="fas fa-arrow-left"></i> Back to Active Tickets
                    @else
                        <i class="fas fa-archive"></i> Archived ({{ $stats['archived'] }})
                    @endif
                </button>
            </div>
        </div>
        
        <div class="card-content">
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th class="th-ticket-number">Ticket #</th>
                            <th class="th-priority">Priority</th>
                            <th class="th-category">Category</th>
                            <th>Subject</th>
                            <th class="th-reporter">Reporter</th>
                            <th class="th-status">Status</th>
                            <th class="th-date">Date</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr class="ticket-row {{ $ticket->status == 'unread' ? 'unread' : '' }}">
                                <td class="td-clickable" onclick="viewTicket({{ $ticket->ticket_id }})">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                </td>
                                <td>
                                    <span class="priority-badge priority-{{ $ticket->priority }}">
                                        @if($ticket->priority == 'urgent')
                                            <i class="fas fa-fire"></i>
                                        @else
                                            <i class="fas fa-info-circle"></i>
                                        @endif
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="category-pill">{{ Str::limit($ticket->category_name, 20) }}</span>
                                </td>
                                <td class="td-clickable" onclick="viewTicket({{ $ticket->ticket_id }})">
                                    {{ Str::limit($ticket->subject, 40) }}
                                </td>
                                <td>{{ $ticket->reporter_email }}</td>
                                <td>
                                    <span class="status-badge status-{{ $ticket->status }}">
                                        @if($ticket->status == 'unread')
                                            <i class="fas fa-envelope"></i>
                                        @elseif($ticket->status == 'read')
                                            <i class="fas fa-envelope-open"></i>
                                        @else
                                            <i class="fas fa-check-circle"></i>
                                        @endif
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn edit" onclick="viewTicket({{ $ticket->ticket_id }})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($ticket->status !== 'resolved' && !$ticket->archived_at)
                                            <button class="action-btn btn-resolve-quick" onclick="resolveTicketQuick({{ $ticket->ticket_id }})" title="Mark as Resolved">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if($ticket->archived_at)
                                            <button class="action-btn btn-unarchive" onclick="deleteTicketQuick({{ $ticket->ticket_id }})" title="Unarchive">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button class="action-btn delete" onclick="permanentDeleteTicket({{ $ticket->ticket_id }})" title="Permanent Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <button class="action-btn delete" onclick="deleteTicketQuick({{ $ticket->ticket_id }})" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    @if(request('search'))
                                        <i class="fas fa-search empty-state-icon"></i>
                                        <p class="empty-state-title">No tickets found</p>
                                        <p class="empty-state-subtitle">Try adjusting your search term: "{{ request('search') }}"</p>
                                        <button 
                                            onclick="window.location.href='{{ route('admin.support-tickets') }}?filter={{ request('filter', 'all') }}'" 
                                            class="empty-state-clear-btn"
                                        >
                                            <i class="fas fa-times"></i> Clear Search
                                        </button>
                                    @else
                                        <i class="fas fa-inbox empty-state-icon"></i>
                                        <p class="empty-state-title">No tickets found</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($tickets, 'links') && $tickets->total() > 0)
            <div class="pagination-container">
                <div class="pagination-info-text">
                    Showing <strong>{{ $tickets->firstItem() ?? 0 }}</strong> to <strong>{{ $tickets->lastItem() ?? 0 }}</strong> of <strong>{{ $tickets->total() }}</strong> tickets
                    @php
                        $activeFilters = collect([
                            request('search') ? 'search' : null,
                            request('priority') ? 'priority' : null,
                            request('status') ? 'status' : null,
                            request('category') ? 'category' : null,
                            request('date_from') ? 'date range' : null,
                        ])->filter()->count();
                    @endphp
                    @if($activeFilters > 0)
                        <span class="pagination-info-highlight"> ({{ $activeFilters }} filter{{ $activeFilters > 1 ? 's' : '' }} active)</span>
                    @endif
                </div>
                <div class="pagination-links">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/admin/support-tickets.js') }}?v={{ filemtime(public_path('js/admin/support-tickets.js')) }}"></script>
@endpush


