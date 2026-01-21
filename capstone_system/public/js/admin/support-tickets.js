// Support Tickets JavaScript
let searchTimeout;

// Get current filter parameters
function getCurrentFilters() {
    const form = document.querySelector('.filters-form');
    return {
        filter: new URLSearchParams(window.location.search).get('filter') || 'all',
        search: form?.querySelector('[name="search"]')?.value || '',
        priority: form?.querySelector('[name="priority"]')?.value || '',
        status: form?.querySelector('[name="status"]')?.value || '',
        category: form?.querySelector('[name="category"]')?.value || '',
        date_from: form?.querySelector('[name="date_from"]')?.value || '',
        date_to: form?.querySelector('[name="date_to"]')?.value || ''
    };
}

// Load tickets dynamically
function loadTickets(params = {}) {
    const filters = { ...getCurrentFilters(), ...params };
    const queryString = new URLSearchParams(filters).toString();
    
    // Show loading state
    const tbody = document.querySelector('.users-table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #5cb85c;"></i>
                    <p style="margin-top: 1rem; color: #6b7280;">Loading tickets...</p>
                </td>
            </tr>
        `;
    }
    
    // Fetch tickets
    fetch(`/admin/support-tickets?${queryString}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateTicketsTable(data.tickets);
        updateStats(data.stats);
        updatePagination(data.pagination);
        updateURL(filters);
    })
    .catch(error => {
        console.error('Error loading tickets:', error);
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-exclamation-triangle empty-state-icon" style="color: #ef4444;"></i>
                        <p class="empty-state-title">Error loading tickets</p>
                        <p class="empty-state-subtitle">Please try refreshing the page</p>
                    </td>
                </tr>
            `;
        }
    });
}

// Update tickets table
function updateTicketsTable(tickets) {
    const tbody = document.querySelector('.users-table tbody');
    if (!tbody) return;
    
    if (!tickets || tickets.length === 0) {
        const searchTerm = getCurrentFilters().search;
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state">
                    ${searchTerm ? `
                        <i class="fas fa-search empty-state-icon"></i>
                        <p class="empty-state-title">No tickets found</p>
                        <p class="empty-state-subtitle">Try adjusting your search term: "${searchTerm}"</p>
                        <button onclick="clearSearch()" class="empty-state-clear-btn">
                            <i class="fas fa-times"></i> Clear Search
                        </button>
                    ` : `
                        <i class="fas fa-inbox empty-state-icon"></i>
                        <p class="empty-state-title">No tickets found</p>
                    `}
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = tickets.map(ticket => `
        <tr class="ticket-row ${ticket.status === 'unread' ? 'unread' : ''}">
            <td class="td-clickable" onclick="viewTicket(${ticket.ticket_id})">
                <strong>${ticket.ticket_number}</strong>
            </td>
            <td>
                <span class="priority-badge priority-${ticket.priority}">
                    <i class="fas fa-${ticket.priority === 'urgent' ? 'fire' : 'info-circle'}"></i>
                    ${ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1)}
                </span>
            </td>
            <td>
                <span class="category-pill">${truncate(ticket.category_name, 20)}</span>
            </td>
            <td class="td-clickable" onclick="viewTicket(${ticket.ticket_id})">
                ${truncate(ticket.subject, 40)}
            </td>
            <td>${ticket.reporter_email}</td>
            <td>
                <span class="status-badge status-${ticket.status}">
                    <i class="fas fa-${ticket.status === 'unread' ? 'envelope' : ticket.status === 'read' ? 'envelope-open' : 'check-circle'}"></i>
                    ${ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}
                </span>
            </td>
            <td>${formatDate(ticket.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit" onclick="viewTicket(${ticket.ticket_id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${ticket.status !== 'resolved' && !ticket.archived_at ? `
                        <button class="action-btn btn-resolve-quick" onclick="resolveTicketQuick(${ticket.ticket_id})" title="Mark as Resolved">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                    ${ticket.archived_at ? `
                        <button class="action-btn btn-unarchive" onclick="deleteTicketQuick(${ticket.ticket_id})" title="Unarchive">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button class="action-btn delete" onclick="permanentDeleteTicket(${ticket.ticket_id})" title="Permanent Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : `
                        <button class="action-btn delete" onclick="deleteTicketQuick(${ticket.ticket_id})" title="Archive">
                            <i class="fas fa-archive"></i>
                        </button>
                    `}
                </div>
            </td>
        </tr>
    `).join('');
}

// Update stats
function updateStats(stats) {
    if (!stats) return;
    
    // Update filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        const filterType = tab.textContent.toLowerCase();
        if (filterType.includes('all')) {
            tab.innerHTML = `<i class="fas fa-list"></i> All Active (${stats.total || 0})`;
        } else if (filterType.includes('unread')) {
            tab.innerHTML = `<i class="fas fa-exclamation-circle"></i> Unread (${stats.unread || 0})`;
        } else if (filterType.includes('urgent')) {
            tab.innerHTML = `<i class="fas fa-fire"></i> Urgent (${stats.urgent || 0})`;
        } else if (filterType.includes('resolved')) {
            tab.innerHTML = `<i class="fas fa-check-circle"></i> Resolved (${stats.resolved || 0})`;
        }
    });
    
    // Update header counts
    const headerActions = document.querySelector('.header-actions');
    if (headerActions) {
        const activeBtn = headerActions.querySelector('.btn-count:first-child');
        const unreadBtn = headerActions.querySelector('.btn-count:nth-child(2)');
        
        if (activeBtn) {
            activeBtn.innerHTML = `<i class="fas fa-ticket-alt"></i> ${stats.total || 0} active`;
        }
        
        if (stats.unread > 0) {
            if (unreadBtn) {
                unreadBtn.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${stats.unread} unread`;
            } else if (activeBtn) {
                activeBtn.insertAdjacentHTML('afterend', `
                    <button class="btn-count" style="background: #ef4444; color: white;">
                        <i class="fas fa-exclamation-circle"></i> ${stats.unread} unread
                    </button>
                `);
            }
        } else if (unreadBtn) {
            unreadBtn.remove();
        }
    }
    
    // Update archive button
    const archiveBtn = document.querySelector('.btn-archive-toggle');
    if (archiveBtn && !archiveBtn.classList.contains('active')) {
        const archivedCount = stats.archived || 0;
        archiveBtn.innerHTML = `<i class="fas fa-archive"></i> View Archived (${archivedCount})`;
    }
}

// Update pagination
function updatePagination(pagination) {
    const container = document.querySelector('.pagination-container');
    if (!container || !pagination) return;
    
    const infoText = container.querySelector('.pagination-info-text');
    if (infoText && pagination.total > 0) {
        const activeFilters = [
            getCurrentFilters().search,
            getCurrentFilters().priority,
            getCurrentFilters().status,
            getCurrentFilters().category,
            getCurrentFilters().date_from
        ].filter(f => f).length;
        
        infoText.innerHTML = `
            Showing <strong>${pagination.from || 0}</strong> to <strong>${pagination.to || 0}</strong> of <strong>${pagination.total}</strong> tickets
            ${activeFilters > 0 ? `<span class="pagination-info-highlight"> (${activeFilters} filter${activeFilters > 1 ? 's' : ''} active)</span>` : ''}
        `;
    }
    
    // Update pagination links
    const linksContainer = container.querySelector('.pagination-links');
    if (linksContainer && pagination.links) {
        linksContainer.innerHTML = pagination.links;
    }
}

// Update URL without page reload
function updateURL(filters) {
    const url = new URL(window.location.href);
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            url.searchParams.set(key, filters[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    window.history.pushState({}, '', url);
}

// Helper functions
function truncate(str, length) {
    return str && str.length > length ? str.substring(0, length) + '...' : str;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Debounced search function
function handleSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadTickets({ search: document.querySelector('[name="search"]').value });
    }, 500);
}

// Clear search
function clearSearch() {
    const searchInput = document.querySelector('[name="search"]');
    if (searchInput) {
        searchInput.value = '';
        loadTickets({ search: '' });
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
    }
    
    // Add change listeners to filter selects
    const filterSelects = document.querySelectorAll('.filter-select, .filter-date-input');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            loadTickets();
        });
    });
});

// Filter tickets by tab
function filterTickets(filter) {
    loadTickets({ filter: filter });
}

// View ticket details
function viewTicket(ticketId) {
    fetch(`/admin/support-tickets/${ticketId}`)
        .then(response => response.json())
        .then(ticket => {
            const htmlContent = `
                <div style="text-align: left; max-height: 450px; overflow-y: auto; padding: 0 1rem;">
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase;">Ticket Information</div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 0.75rem;">
                            <div>
                                <strong style="color: #6b7280;">Status:</strong> 
                                <span class="status-badge status-${ticket.status}">${ticket.status.toUpperCase()}</span>
                            </div>
                            <div>
                                <strong style="color: #6b7280;">Priority:</strong> 
                                <span class="priority-badge priority-${ticket.priority}">${ticket.priority.toUpperCase()}</span>
                            </div>
                            <div>
                                <strong style="color: #6b7280;">Category:</strong> 
                                <span class="category-pill">${ticket.category.replace(/_/g, ' ').toUpperCase()}</span>
                            </div>
                            <div>
                                <strong style="color: #6b7280;">Submitted:</strong> 
                                <span style="color: #374151;">${new Date(ticket.created_at).toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase;">Reporter Information</div>
                        <div style="color: #6b7280; line-height: 1.6;">
                            <strong>Email:</strong> ${ticket.reporter_email}<br>
                            <strong>Name:</strong> ${ticket.reporter_name || 'N/A'}<br>
                            <strong>IP Address:</strong> ${ticket.reporter_ip || 'N/A'}
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase;">Subject</div>
                        <div style="color: #374151; font-weight: 500;">${ticket.subject}</div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase;">Description</div>
                        <div style="color: #6b7280; line-height: 1.6; white-space: pre-wrap;">${ticket.description}</div>
                    </div>
                    
                    ${ticket.screenshot_path ? `
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase;">Screenshot</div>
                        <img src="/storage/${ticket.screenshot_path}" alt="Screenshot" style="max-width: 100%; border-radius: 8px; border: 1px solid #e5e7eb;">
                    </div>
                    ` : ''}
                    
                    <div style="margin-bottom: 0;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase;">Admin Notes</div>
                        <textarea id="swal-admin-notes" class="swal2-textarea" placeholder="Add internal notes about this ticket..." style="width: 100%; min-height: 100px; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; resize: vertical; font-size: 0.875rem;">${ticket.admin_notes || ''}</textarea>
                    </div>
                </div>
            `;
            
            Swal.fire({
                title: `<strong>${ticket.ticket_number}</strong>`,
                html: htmlContent,
                width: '700px',
                showCloseButton: true,
                showConfirmButton: true,
                showDenyButton: ticket.archived_at ? true : true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save"></i> Save Notes',
                confirmButtonColor: '#10b981',
                denyButtonText: ticket.archived_at ? '<i class="fas fa-undo"></i> Unarchive' : '<i class="fas fa-archive"></i> Archive',
                denyButtonColor: ticket.archived_at ? '#f59e0b' : '#ef4444',
                cancelButtonText: ticket.status !== 'resolved' && !ticket.archived_at ? '<i class="fas fa-check"></i> Resolve' : '<i class="fas fa-times"></i> Close',
                cancelButtonColor: ticket.status !== 'resolved' && !ticket.archived_at ? '#3b82f6' : '#6b7280',
                customClass: {
                    popup: 'ticket-swal-popup',
                    confirmButton: 'swal-btn-order-2',
                    denyButton: 'swal-btn-order-3',
                    cancelButton: 'swal-btn-order-1'
                },
                preConfirm: () => {
                    return document.getElementById('swal-admin-notes').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Save notes
                    saveAdminNotes(ticket.ticket_id, result.value);
                } else if (result.dismiss === Swal.DismissReason.cancel && ticket.status !== 'resolved' && !ticket.archived_at) {
                    // Resolve ticket
                    confirmResolveTicket(ticket.ticket_id);
                } else if (result.isDenied) {
                    // Archive/Unarchive ticket
                    if (ticket.archived_at) {
                        confirmUnarchiveTicket(ticket.ticket_id);
                    } else {
                        confirmArchiveTicket(ticket.ticket_id);
                    }
                }
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load ticket details',
                confirmButtonColor: '#ef4444'
            });
            console.error('Error:', error);
        });
}

// Save admin notes
function saveAdminNotes(ticketId, notes) {
    fetch(`/admin/support-tickets/${ticketId}/update-notes`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ admin_notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: 'Admin notes updated successfully',
                timer: 1500,
                showConfirmButton: false
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save notes',
            confirmButtonColor: '#ef4444'
        });
        console.error('Error:', error);
    });
}

// Resolve ticket (quick action)
function resolveTicketQuick(ticketId) {
    event.stopPropagation();
    confirmResolveTicket(ticketId);
}

// Confirm resolve ticket
function confirmResolveTicket(ticketId) {
    Swal.fire({
        title: 'Resolve Ticket?',
        text: "This will mark the ticket as resolved.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, resolve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/support-tickets/${ticketId}/resolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Resolved!',
                        text: 'Ticket has been marked as resolved',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to resolve ticket',
                    confirmButtonColor: '#ef4444'
                });
                console.error('Error:', error);
            });
        }
    });
}

// Delete/Unarchive ticket (quick action)
function deleteTicketQuick(ticketId, isArchived = false) {
    event.stopPropagation();
    
    // Check if ticket is archived by fetching ticket data
    fetch(`/admin/support-tickets/${ticketId}`)
        .then(response => response.json())
        .then(ticket => {
            if (ticket.archived_at) {
                confirmUnarchiveTicket(ticketId);
            } else {
                confirmArchiveTicket(ticketId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback to archive if fetch fails
            confirmArchiveTicket(ticketId);
        });
}

// Confirm archive ticket
function confirmArchiveTicket(ticketId) {
    Swal.fire({
        title: 'Archive Ticket?',
        text: "Ticket will be moved to archived section for historical reference.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, archive it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/support-tickets/${ticketId}/archive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archived!',
                        text: 'Ticket has been archived',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to archive ticket',
                    confirmButtonColor: '#ef4444'
                });
                console.error('Error:', error);
            });
        }
    });
}

// Confirm unarchive ticket
function confirmUnarchiveTicket(ticketId) {
    Swal.fire({
        title: 'Unarchive Ticket?',
        text: "Ticket will be restored to active tickets.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, unarchive it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/support-tickets/${ticketId}/unarchive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Unarchived!',
                        text: 'Ticket has been restored',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to unarchive ticket',
                    confirmButtonColor: '#ef4444'
                });
                console.error('Error:', error);
            });
        }
    });
}

// Permanent delete ticket (only for archived tickets)
function permanentDeleteTicket(ticketId) {
    event.stopPropagation();
    
    Swal.fire({
        title: 'Permanently Delete?',
        html: `<div style="text-align: left;">
            <p style="color: #ef4444; font-weight: 600; margin-bottom: 0.5rem;">
                <i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone!
            </p>
            <p style="color: #6b7280; margin: 0;">
                The ticket will be permanently removed from the database. 
                This is irreversible and cannot be recovered.
            </p>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete permanently!',
        cancelButtonText: 'Cancel',
        focusCancel: true,
        customClass: {
            confirmButton: 'swal-btn-danger'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/support-tickets/${ticketId}/permanent-delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Ticket has been permanently deleted',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete ticket',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete ticket',
                    confirmButtonColor: '#ef4444'
                });
                console.error('Error:', error);
            });
        }
    });
}
