@extends('layouts.app')

@section('page-heading', 'System History Logs')
@section('page-subheading', 'Detailed audit trail of all administrative and system actions.')

@push('styles')
<style>
    /* ── Premium Log Aesthetics ── */
    :root {
        --log-bg: #f8fafc;
        --log-card: #ffffff;
        --log-border: #e2e8f0;
        --log-accent: #f59e0b;
        --log-text-main: #1e293b;
        --log-text-muted: #64748b;
    }

    .log-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ── Advanced Filter Bar ── */
    .filter-card {
        background: white;
        border: 1px solid var(--log-border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .log-input {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        padding: 0.6rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
        width: 100%;
        transition: all 0.2s;
    }

    .log-input:focus {
        border-color: var(--log-accent);
        background: white;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        outline: none;
    }

    /* ── Log Table ── */
    .log-table-wrapper {
        background: white;
        border: 1px solid var(--log-border);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
    }

    .log-table {
        width: 100%;
        border-collapse: collapse;
    }

    .log-table th {
        background: #f8fafc;
        padding: 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--log-text-muted);
        border-bottom: 1px solid var(--log-border);
    }

    .log-table td {
        padding: 1.25rem 1rem;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }

    .log-row:hover {
        background: #fdfaf3;
    }

    /* ── Action Badges ── */
    .action-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .badge-create { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-update { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-delete { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-auth   { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .badge-admin  { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }

    /* ── Metadata ── */
    .user-avatar {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: white;
        font-size: 0.75rem;
    }

    .timestamp {
        color: #94a3b8;
        font-family: 'JetBrains Mono', 'Monaco', monospace;
        font-size: 0.75rem;
    }

    .notes-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        color: #475569;
        margin-top: 0.25rem;
        max-width: 400px;
        max-height: 100px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="log-container">
    {{-- Search & Filters --}}
    <div class="filter-card">
        <form action="{{ route('activity-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1 px-1">Search Keywords</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="log-input pl-10" placeholder="Names, emails, actions, notes...">
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1 px-1">Category</label>
                <select name="type" class="log-input" onchange="this.form.submit()">
                    <option value="">All Activities</option>
                    <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Login/Security</option>
                    <option value="admin" {{ request('type') === 'admin' ? 'selected' : '' }}>Admin Actions</option>
                    <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>System Logic</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1 px-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="log-input">
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1 px-1">To Date</label>
                <div class="flex gap-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="log-input">
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white p-2.5 rounded-xl transition-all">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                    <a href="{{ route('activity-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 p-2.5 rounded-xl transition-all">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Log Table --}}
    <div class="log-table-wrapper">
        <table class="log-table">
            <thead>
                <tr>
                    <th class="pl-6">Timestamp</th>
                    <th>User & Role</th>
                    <th>Action</th>
                    <th>Notes & Details</th>
                    <th class="pr-6">Source</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="log-row">
                    <td class="pl-6">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">{{ $log->created_at->format('M d, Y') }}</span>
                            <span class="timestamp">{{ $log->created_at->format('h:i:s A') }}</span>
                            <span class="text-[10px] text-slate-400 uppercase tracking-tighter mt-1">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="user-avatar shadow-sm">
                                {{ strtoupper(substr($log->user_name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900 leading-tight">{{ $log->user_name ?? 'System' }}</div>
                                <div class="text-xs text-slate-500">{{ $log->user_email ?? 'automated@task.system' }}</div>
                                <div class="mt-1">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold border border-slate-200">
                                        {{ strtoupper($log->user_role ?? 'System') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $action = strtolower($log->action);
                            $class = 'badge-admin';
                            $icon = 'activity';
                            
                            // Category: Creation/Addition
                            if (str_contains($action, 'create') || str_contains($action, 'add') || str_contains($action, 'recorded')) { 
                                $class = 'badge-create'; 
                                $icon = 'plus-circle'; 
                            }
                            // Category: Updates
                            elseif (str_contains($action, 'edit') || str_contains($action, 'update') || str_contains($action, 'change') || str_contains($action, 'toggle')) { 
                                $class = 'badge-update'; 
                                $icon = 'edit-3'; 
                            }
                            // Category: Deletion/Archive/Rejection
                            elseif (str_contains($action, 'delete') || str_contains($action, 'reject') || str_contains($action, 'archive') || str_contains($action, 'dismissed')) { 
                                $class = 'badge-delete'; 
                                $icon = 'trash-2'; 
                            }
                            // Category: Financial/Payment
                            elseif (str_contains($action, 'payment') || str_contains($action, 'salary') || str_contains($action, 'expense')) { 
                                $class = 'badge-update'; 
                                $icon = 'philippine-peso'; 
                            }
                            // Category: Restoration
                            elseif (str_contains($action, 'restore')) { 
                                $class = 'badge-create'; 
                                $icon = 'rotate-ccw'; 
                            }
                            // Category: Approval
                            elseif (str_contains($action, 'approve')) { 
                                $class = 'badge-create'; 
                                $icon = 'check-circle'; 
                            }
                            // Category: Security/Auth (if visible)
                            elseif (str_contains($action, 'login') || str_contains($action, 'logout')) { 
                                $class = 'badge-auth'; 
                                $icon = 'key'; 
                            }

                            // Module Specific Icons (Override if needed)
                            if (str_contains($action, 'maintenance')) $icon = 'wrench';
                            elseif (str_contains($action, 'boundary')) $icon = 'philippine-peso';
                            elseif (str_contains($action, 'driver')) $icon = 'users';
                            elseif (str_contains($action, 'unit')) $icon = 'car';
                            elseif (str_contains($action, 'incident')) $icon = 'alert-triangle';
                            elseif (str_contains($action, 'staff')) $icon = 'user-cog';
                            elseif (str_contains($action, 'coding')) $icon = 'calendar';
                            elseif (str_contains($action, 'franchise')) $icon = 'file-text';
                        @endphp
                        <span class="action-badge {{ $class }}">
                            <i data-lucide="{{ $icon }}" class="w-3 h-3"></i>
                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </td>
                    <td>
                        @if($log->notes)
                            <div class="notes-box">
                                {!! nl2br(e($log->notes)) !!}
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">No additional details</span>
                        @endif
                    </td>
                    <td class="pr-6">
                        <div class="flex flex-col items-end">
                            <code class="text-[10px] bg-slate-50 text-slate-600 border border-slate-200 px-2 py-1 rounded">
                                {{ $log->ip_address }}
                            </code>
                            <div class="text-[9px] text-slate-400 mt-1 max-w-[120px] truncate" title="{{ $log->user_agent }}">
                                {{ $log->user_agent }}
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <div class="flex flex-col items-center opacity-40">
                            <i data-lucide="database-backup" class="w-16 h-16 mb-4"></i>
                            <h3 class="text-xl font-bold">No History Logs Found</h3>
                            <p class="text-sm">Try adjusting your filters or search keywords.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-slate-50 border-t">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
