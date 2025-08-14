@extends('layouts.dashboard')

@section('title', 'Inventory Transactions')

@section('page-title', 'Inventory Transactions')
@section('page-subtitle', 'Track all inventory movements and transactions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-inventory-transactions.css') }}">
@endpush

@section('navigation')
    @include('partials.navigation')
@endsection

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt"></i>
                Inventory Transactions
            </h3>
            <div class="card-actions">
                <button class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-sync"></i>
                    Refresh
                </button>
                <button class="btn btn-primary" onclick="exportTransactions()">
                    <i class="fas fa-download"></i>
                    Export
                </button>
            </div>
        </div>
        
        <div class="card-content">
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>User</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">#{{ $transaction->transaction_id }}</span>
                                    </td>
                                    <td>
                                        <div class="item-info-cell">
                                            @if($transaction->inventoryItem)
                                                <div class="item-details">
                                                    <div class="item-name">{{ $transaction->inventoryItem->item_name }}</div>
                                                    <div class="item-category">{{ $transaction->inventoryItem->category->category_name ?? 'Unknown Category' }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">Item not found</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $typeClass = match(strtolower($transaction->transaction_type)) {
                                                'in', 'received', 'stock_in' => 'success',
                                                'out', 'dispensed', 'stock_out' => 'danger',
                                                'transfer' => 'warning',
                                                'adjustment' => 'info',
                                                default => 'secondary'
                                            };
                                            $typeIcon = match(strtolower($transaction->transaction_type)) {
                                                'in', 'received', 'stock_in' => 'fas fa-arrow-down',
                                                'out', 'dispensed', 'stock_out' => 'fas fa-arrow-up',
                                                'transfer' => 'fas fa-exchange-alt',
                                                'adjustment' => 'fas fa-edit',
                                                default => 'fas fa-circle'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $typeClass }}">
                                            <i class="{{ $typeIcon }}"></i>
                                            {{ ucfirst($transaction->transaction_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="quantity-cell">
                                            <span class="quantity-value">{{ $transaction->quantity }}</span>
                                            @if($transaction->inventoryItem)
                                                <small class="quantity-unit">{{ $transaction->inventoryItem->unit ?? 'units' }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-info-cell">
                                            @if($transaction->user)
                                                <div class="user-avatar-small">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-details-small">
                                                    <div class="user-name">{{ $transaction->user->first_name }} {{ $transaction->user->last_name }}</div>
                                                    <div class="user-role">{{ $transaction->user->role->role_name ?? 'Unknown' }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown User</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($transaction->patient)
                                            <div class="patient-info-cell">
                                                <div class="patient-name">{{ $transaction->patient->first_name }} {{ $transaction->patient->last_name }}</div>
                                                <div class="patient-id">ID: {{ $transaction->patient->patient_id }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="timestamp-cell">
                                            <div class="timestamp">{{ $transaction->transaction_date ? $transaction->transaction_date->format('M d, Y') : 'N/A' }}</div>
                                            <div class="time">{{ $transaction->transaction_date ? $transaction->transaction_date->format('h:i A') : 'N/A' }}</div>
                                            <small class="text-muted">{{ $transaction->transaction_date ? $transaction->transaction_date->diffForHumans() : 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="remarks-cell">
                                            {{ $transaction->remarks ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3 class="empty-state-title">No Transactions Found</h3>
                    <p class="empty-state-description">
                        No inventory transaction
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/admin-inventory-transactions.js') }}"></script>
@endpush
