@extends('layouts.app')

@section('page-heading', 'Owner Control Center')
@section('page-subheading', 'System administration, user management & security audit — Owner only')

@section('content')
<style>
    /* ── Premium dark glass palette ── */
    :root {
        --sa-bg:       #f8fafc;
        --sa-surface:  #f1f5f9;
        --sa-card:     #ffffff;
        --sa-border:   #e2e8f0;
        --sa-gold:     #ca8a04;
        --sa-gold-dim: #fef3c7;
        --sa-teal:     #0d9488;
        --sa-purple:   #7c3aed;
        --sa-red:      #dc2626;
        --sa-green:    #16a34a;
        --sa-text:     #1e293b;
        --sa-muted:    #64748b;
    }

    .sa-shell {
        background: var(--sa-bg);
        min-height: calc(100vh - 60px);
        color: var(--sa-text);
        font-family: 'Inter', sans-serif;
    }

    /* ── Tabs ── */
    .sa-tab-bar { border-bottom: 1px solid var(--sa-border); }
    .sa-tab {
        padding: .6rem 1.25rem;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--sa-muted);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all .2s;
        white-space: nowrap;
    }
    .sa-tab.active, .sa-tab:hover { color: var(--sa-gold); border-color: var(--sa-gold); }

    /* ── Stat cards ── */
    .sa-stat {
        background: linear-gradient(135deg, var(--sa-card) 0%, #f8fafc 100%);
        border: 1px solid var(--sa-border);
        border-radius: 1.5rem;
        padding: 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }
    .sa-stat:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,.08); }
    .sa-stat::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 120px; height: 120px;
        border-radius: 50%;
        opacity: .06;
        background: currentColor;
    }

    /* ── Tables ── */
    .sa-table { width: 100%; border-collapse: collapse; }
    .sa-table th {
        background: #f8fafc;
        color: var(--sa-muted);
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        padding: .75rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--sa-border);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .sa-table td {
        padding: .85rem 1rem;
        font-size: .825rem;
        border-bottom: 1px solid var(--sa-border);
        vertical-align: middle;
    }
    .sa-table tbody tr:hover { background: rgba(0,0,0,.015); }

    /* ── Badges (High Contrast Light Mode) ── */
    .badge-pending  { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
    .badge-approved { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
    .badge-rejected { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
    .badge-login    { background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; }
    .badge-logout   { background:#f4f4f5; color:#52525b; border:1px solid #d4d4d8; }
    .badge-failed   { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; }
    .badge-role-super_admin { background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe; }
    .badge-role-manager     { background:#e0f2fe; color:#075985; border:1px solid #7dd3fc; }
    .badge-role-dispatcher  { background:#ccfbf1; color:#115e59; border:1px solid #5eead4; }
    .badge-role-secretary   { background:#e0e7ff; color:#3730a3; border:1px solid #a5b4fc; }
    .badge-role-staff       { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }

    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .67rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    /* ── Buttons ── */
    .btn-approve { background:#166534; color:#4ade80; border:1px solid #15803d; border-radius:.5rem; padding:.3rem .9rem; font-size:.72rem; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-approve:hover { background:#15803d; }
    .btn-reject  { background:#7f1d1d; color:#f87171; border:1px solid #991b1b; border-radius:.5rem; padding:.3rem .9rem; font-size:.72rem; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-reject:hover  { background:#991b1b; }
    .btn-ghost   { background:transparent; color:var(--sa-muted); border:1px solid var(--sa-border); border-radius:.5rem; padding:.3rem .9rem; font-size:.72rem; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-ghost:hover   { background:rgba(0,0,0,.04); color:var(--sa-text); }
    .btn-gold   { background:var(--sa-gold); color:#ffffff; border:0; border-radius:.5rem; padding:.35rem 1rem; font-size:.72rem; font-weight:800; cursor:pointer; transition:all .2s; }
    .btn-gold:hover   { background:#fbbf24; }
    .btn-danger { background:#7f1d1d; color:#f87171; border:1px solid #991b1b; border-radius:.5rem; padding:.3rem .9rem; font-size:.72rem; font-weight:700; cursor:pointer; transition:all .2s; }

    /* ── Search & inputs ── */
    .sa-input {
        background: #f8fafc;
        border: 1px solid var(--sa-border);
        color: var(--sa-text);
        border-radius: .6rem;
        padding: .5rem 1rem;
        font-size: .82rem;
        outline: none;
        transition: border-color .2s;
        width: 100%;
    }
    .sa-input:focus { border-color: var(--sa-gold); }

    /* ── Page access toggle chips ── */
    .page-chip {
        cursor: pointer;
        padding: .3rem .75rem;
        border-radius: .45rem;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        border: 1px solid var(--sa-border);
        background: #ffffff;
        color: var(--sa-muted);
        transition: all .2s;
        user-select: none;
    }
    .page-chip.active { background: #78350f44; color: var(--sa-gold); border-color: #92400e; }
    .page-chip:hover  { border-color: var(--sa-gold); color: var(--sa-gold); }

    /* ── Audit timeline dot ── */
    .audit-dot-login  { background: #3b82f6; }
    .audit-dot-logout { background: #6b7280; }
    .audit-dot-failed_login { background: #ef4444; }
    .audit-dot-approved { background: #22c55e; }
    .audit-dot-rejected { background: #a855f7; }

    /* ── Toast notification ── */
    #sa-toast {
        position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(4rem);
        background: #1e293b; border: 1px solid var(--sa-gold); color: #ffffff;
        padding: .85rem 1.75rem; border-radius: 999px; font-size: .85rem; font-weight: 600;
        box-shadow: 0 12px 40px rgba(0,0,0,.6);
        z-index: 9999; transition: transform .4s cubic-bezier(.34,1.56,.64,1);
        max-width: 90vw; display: flex; align-items: center; gap: .75rem;
    }
    #sa-toast.show { transform: translateX(-50%) translateY(0); }
    #sa-toast.error { border-color: #ef4444; }

    /* ── Modal ── */
    .sa-modal-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,.75); backdrop-filter: blur(4px);
        z-index: 9990; display: none; align-items: center; justify-content: center;
    }
    .sa-modal-backdrop.open { display: flex; }
    .sa-modal {
        background: var(--sa-card); border: 1px solid var(--sa-border); border-radius: 2rem;
        padding: 2rem; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 24px 80px rgba(0,0,0,.7);
        animation: modal-in .25s ease;
    }
    @keyframes modal-in { from { opacity:0; transform:scale(.94) translateY(1rem); } to { opacity:1; transform:none; } }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
</style>
@php
    use App\Http\Controllers\SuperAdminController;
    $pages = SuperAdminController::$pageDefinitions;
    
    // Grouping manually to ensure we keep the associative keys (route patterns)
    $groups = [];
    foreach ($pages as $pattern => $def) {
        $g = $def['group'] ?? 'Other';
        $groups[$g][$pattern] = $def;
    }
    ksort($groups); // Sort by group name (1, 2, 3...)
@endphp

<div class="sa-shell p-0">

    {{-- ══ Header Banner ══ --}}
    <div style="background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); border: 1px solid #fde047; border-radius: 1.5rem; margin: -0.5rem 1.25rem 0 1.25rem; position: relative; z-index: 10;" class="px-6 pt-5 pb-0">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-4">
                <div style="background:linear-gradient(135deg,#f59e0b,#d97706); width:52px; height:52px; border-radius:1.25rem;" class="flex items-center justify-center shadow-lg flex-shrink-0">
                    <i data-lucide="crown" style="width:26px;height:26px;color:#1c1917;"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <h1 style="color:#854d0e; font-size:1.35rem; font-weight:900; letter-spacing:-.02em;">Owner Control Center</h1>
                        <span class="badge badge-role-super_admin">Owner</span>
                    </div>
                    <p style="color:#71717a; font-size:.8rem;">Welcome back, <strong style="color:var(--sa-text);">{{ auth()->user()->full_name }}</strong> · Full system access</p>
                </div>
            </div>
            <div class="hidden lg:flex items-center gap-3 text-right">

                <div>
                    <p style="color:#16a34a; font-size:1.4rem; font-weight:900;">{{ $activeUsers }}</p>
                    <p style="color:#71717a; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">Active</p>
                </div>
                <div style="width:1px; height:36px; background:#dcdcdc;"></div>
                <div>
                    <p style="color:#000; font-size:1.4rem; font-weight:900;">{{ $totalUsers }}</p>
                    <p style="color:#71717a; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">Total Users</p>
                </div>
            </div>
        </div>

        {{-- Tab Bar --}}
        <div class="sa-tab-bar flex gap-1 overflow-x-auto">
            <button class="sa-tab {{ $tab === 'overview' ? 'active' : '' }}" onclick="switchTab('overview')">
                <i data-lucide="layout-dashboard" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>Overview
            </button>
            <button class="sa-tab {{ $tab === 'staff' ? 'active' : '' }}" onclick="switchTab('staff')">
                <i data-lucide="user-plus" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>Create Staff
            </button>
            <button class="sa-tab {{ $tab === 'users' ? 'active' : '' }}" onclick="switchTab('users')">
                <i data-lucide="users" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>All Users
            </button>
            <button class="sa-tab {{ $tab === 'access' ? 'active' : '' }}" onclick="switchTab('access')">
                <i data-lucide="shield-check" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>Page Access
            </button>
            <button class="sa-tab {{ $tab === 'audit' ? 'active' : '' }}" onclick="switchTab('audit')">
                <i data-lucide="activity" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>Login History
            </button>
            <button class="sa-tab {{ $tab === 'security' ? 'active' : '' }}" onclick="switchTab('security')">
                <i data-lucide="lock" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>System Security
            </button>
        </div>
    </div>

    {{-- ══ Tab Content ══ --}}
    <div class="p-6">

        {{-- ─── OVERVIEW TAB ─── --}}
        <div id="tab-overview" class="sa-tab-content {{ $tab === 'overview' ? '' : 'hidden' }}">
            {{-- Stat Row --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="sa-stat" style="color:#f59e0b;">
                    <div class="flex items-center justify-between mb-3">
                        <span style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#92400e;">Total Staff</span>
                        <div style="background:rgba(245,158,11,.12); padding:.45rem; border-radius:.6rem;">
                            <i data-lucide="users" style="width:16px;height:16px;color:#f59e0b;"></i>
                        </div>
                    </div>
                    <p style="font-size:2.2rem; font-weight:900; line-height:1; color:#000;">{{ $totalUsers }}</p>
                    <p style="font-size:.7rem; color:#64748b; margin-top:.4rem;">Registered accounts</p>
                </div>

                <div class="sa-stat" style="color:#22c55e;">
                    <div class="flex items-center justify-between mb-3">
                        <span style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#166534;">Active</span>
                        <div style="background:rgba(34,197,94,.12); padding:.45rem; border-radius:.6rem;">
                            <i data-lucide="check-circle" style="width:16px;height:16px;color:#22c55e;"></i>
                        </div>
                    </div>
                    <p style="font-size:2.2rem; font-weight:900; line-height:1; color:#000;">{{ $activeUsers }}</p>
                    <p style="font-size:.7rem; color:#64748b; margin-top:.4rem;">Approved & active</p>
                </div>
                <div class="sa-stat" style="color:#ef4444;">
                    <div class="flex items-center justify-between mb-3">
                        <span style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#991b1b;">Rejected</span>
                        <div style="background:#fee2e2; padding:.45rem; border-radius:.6rem;">
                            <i data-lucide="x-circle" style="width:16px;height:16px;color:#dc2626;"></i>
                        </div>
                    </div>
                    <p style="font-size:2.2rem; font-weight:900; line-height:1; color:#000;">{{ $rejectedUsers }}</p>
                    <p style="font-size:.7rem; color:#64748b; margin-top:.4rem;">Denied access</p>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--sa-border); display:flex; align-items:center; gap:.6rem;">
                    <i data-lucide="activity" style="width:15px;height:15px;color:#f59e0b;"></i>
                    <span style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#475569;">Recent Login Activity</span>
                </div>
                <div style="overflow-x:auto; max-height: 600px; overflow-y: auto;">
                    <table class="sa-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAudit as $audit)
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#000;">{{ $audit->user_name ?? 'Unknown' }}</div>
                                    <div style="font-size:.72rem; color:#64748b;">{{ $audit->user_email ?? '' }}</div>
                                </td>
                                <td>
                                    @php
                                        $aMap = [
                                            'login' => ['badge-login','Login'], 
                                            'logout' => ['badge-logout','Logout'], 
                                            'failed_login' => ['badge-failed','Failed Login'], 
                                            'approved' => ['badge-approved','Approved'], 
                                            'rejected' => ['badge-rejected','Rejected'],
                                            'password_changed' => ['badge-role-manager', 'PW Changed'],
                                            'created' => ['badge-login', 'Created']
                                        ];
                                        [$cls,$lbl] = $aMap[$audit->action] ?? ['badge-logout', $audit->action];
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ $lbl }}</span>
                                </td>
                                <td style="color:#64748b; font-family:monospace; font-size:.78rem;">{{ $audit->ip_address ?? '—' }}</td>
                                <td style="color:#64748b; font-size:.78rem;" title="{{ $audit->created_at }}">{{ \Carbon\Carbon::parse($audit->created_at)->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align:center; color:#64748b; padding:2rem;">No activity recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tab-staff" class="sa-tab-content {{ $tab === 'staff' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Left Side: Info --}}
                <div class="lg:col-span-4">
                    <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; padding:2rem; height:100%;">
                        <div style="background:rgba(245,158,11,.15); width:52px; height:52px; border-radius:1.25rem; display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem;">
                            <i data-lucide="user-plus" style="width:26px;height:26px;color:#f59e0b;"></i>
                        </div>
                        <h3 style="color:#000; font-weight:900; font-size:1.4rem; margin-bottom:.75rem;">Create Staff Account</h3>
                        <p style="color:#64748b; font-size:.88rem; line-height:1.6; margin-bottom:1.5rem;">
                            Add a new member to your team. The system will automatically generate a secure password and send the login credentials directly to their email address.
                        </p>
                        
                        <div style="background:rgba(245,158,11,.05); border:1px solid rgba(245,158,11,.15); border-radius:1rem; padding:1.25rem;">
                            <div class="flex items-center gap-2 mb-2" style="color:#b45309; font-weight:800; font-size:.72rem; text-transform:uppercase; letter-spacing:.05em;">
                                <i data-lucide="shield-check" style="width:14px;height:14px;"></i>
                                Security Note
                            </div>
                            <p style="font-size:.78rem; color:#d97706; line-height:1.5;">
                                For security, new users are required to change their auto-generated password immediately upon their first login.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Form --}}
                <div class="lg:col-span-8">
                    <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; padding:2.5rem;">
                        <form id="staffForm" onsubmit="submitStaff(event)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#475569; display:block; margin-bottom:.6rem;">First Name <span style="color:#ef4444;">*</span></label>
                                    <input type="text" id="staff-first" class="sa-input" required maxlength="16" pattern="^[a-zA-Z\s]+$" title="Only letters and spaces allowed, max 16 characters" placeholder="Enter first name" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '');">
                                </div>
                                <div>
                                    <label style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#475569; display:block; margin-bottom:.6rem;">Last Name <span style="color:#ef4444;">*</span></label>
                                    <input type="text" id="staff-last" class="sa-input" required maxlength="16" pattern="^[a-zA-Z\s]+$" title="Only letters and spaces allowed, max 16 characters" placeholder="Enter last name" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '');">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#475569; display:block; margin-bottom:.6rem;">Email Address <span style="color:#ef4444;">*</span></label>
                                    <input type="email" id="staff-email" class="sa-input" required placeholder="name@eurotaxi.com">
                                </div>
                                <div>
                                    <label style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#475569; display:block; margin-bottom:.6rem;">Phone Number</label>
                                    <input type="text" id="staff-phone" class="sa-input" maxlength="11" pattern="^[0-9]+$" title="Only up to 11 numbers allowed" placeholder="09XX XXX XXXX" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#475569; display:block; margin-bottom:.6rem;">Home Address</label>
                                <input type="text" id="staff-address" class="sa-input" maxlength="255" pattern="^[a-zA-Z0-9\s.,'-]+$" title="Only letters, numbers, spaces, dots, commas, dashes, and single quotes allowed" placeholder="Enter complete home address" oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s.,'-]/g, '');">
                            </div>

                            <div class="mb-8">
                                <div class="flex items-center justify-between mb-2">
                                    <label style="font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#475569; margin-bottom:0;">Assign System Role <span style="color:#ef4444;">*</span></label>
                                    <button type="button" onclick="openManageRolesModal()" class="text-[10px] font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1 bg-amber-50 px-2 py-1 rounded-md border border-amber-100 transition-colors">
                                        <i data-lucide="settings-2" class="w-3 h-3"></i> Manage System Roles
                                    </button>
                                </div>
                                <select id="staff-role" class="sa-input" required>
                                    <option value="" disabled selected>Select a role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-sa-border">
                                <button type="submit" class="btn-gold px-8 py-3 flex items-center gap-2" id="btn-save-staff">
                                    <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
                                    Create Staff Account
                                </button>
                            </div>
                        </form>

                        {{-- Success Output --}}
                        <div id="staffSuccessMsg" class="hidden text-center py-8">
                            <div style="background:#dcfce7; width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                                <i data-lucide="check-circle" style="width:32px;height:32px;color:#22c55e;"></i>
                            </div>
                            <h3 style="color:#15803d; font-weight:900; font-size:1.5rem; margin-bottom:.75rem;">Account Created Successfully!</h3>
                            <p style="color:#64748b; font-size:.9rem; margin-bottom:2rem; max-width:400px; margin-left:auto; margin-right:auto;">
                                The user has been added to the system and an invitation email has been sent.
                            </p>
                            
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:1.25rem; padding:1.5rem; margin-bottom:2rem; max-width:320px; margin-left:auto; margin-right:auto;">
                                <p style="font-size:.65rem; color:#64748b; text-transform:uppercase; font-weight:800; letter-spacing:.1em; margin-bottom:.5rem;">One-Time Password</p>
                                <p id="generatedPassword" style="font-size:1.8rem; color:#000; font-family:monospace; font-weight:900; letter-spacing:.1em;"></p>
                            </div>

                            <button onclick="resetStaffForm()" class="btn-ghost px-8 py-3">Create Another Account</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── ALL USERS TAB ─── --}}
        <div id="tab-users" class="sa-tab-content {{ $tab === 'users' ? '' : 'hidden' }}">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" id="userSearch" class="sa-input pl-10" style="max-width:280px;" placeholder="Search users..." autocomplete="new-password" oninput="filterUserTable(this.value)">
                    </div>
                    <select id="statusFilter" class="sa-input" style="max-width:180px;" onchange="filterUserTable()">
                        <option value="">All Statuses</option>
                        <option value="activated">Activated</option>
                        <option value="pending">Pending Activation</option>
                    </select>
                </div>
                <button class="btn-ghost px-5 py-2.5 flex items-center gap-2" onclick="openArchivesModal()" style="border-color:var(--sa-gold); color:var(--sa-gold);">
                    <i data-lucide="archive" class="w-4 h-4"></i> View Archives
                </button>
            </div>
            <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                <div style="overflow-x:auto; max-height: 700px; overflow-y: auto;">
                    <table class="sa-table" id="userTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Active</th>
                            <th>Last Login</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allUsers->whereNull('deleted_at') as $u)
                        @php
                            $isActivated = !$u->must_change_password && $u->last_login;
                            $statusSlug = $isActivated ? 'activated' : 'pending';
                            $editData = $u->only(['id','first_name','last_name','email','role','phone_number','address']);
                        @endphp
                        <tr class="user-row transition-colors" data-name="{{ strtolower($u->full_name) }}" data-email="{{ strtolower($u->email) }}" data-role="{{ strtolower($u->role) }}" data-status="{{ $statusSlug }}">
                            <td onclick="openUserDetailsModal({{ $u->id }})" style="cursor:pointer;">
                                <div class="flex items-center gap-2.5">
                                    @if($u->profile_image)
                                        <img src="{{ asset('storage/' . $u->profile_image) }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;" alt="">
                                    @else
                                        <div style="width:34px;height:34px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.8rem;color:#64748b;flex-shrink:0;">
                                            {{ strtoupper(substr($u->full_name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700;color:#000;">{{ $u->full_name }}</div>
                                        <div style="font-size:.7rem;color:#64748b;">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-role-{{ $u->role }}">{{ $u->role === 'super_admin' ? 'Owner' : ucfirst(str_replace('_', ' ', $u->role)) }}</span></td>
                            <td>
                                @if($isActivated)
                                    <span class="badge badge-approved">● Activated</span>
                                @else
                                    <span class="badge badge-pending">○ Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($u->is_disabled)
                                    <button onclick="confirmEnable({{ $u->id }}, '{{ addslashes($u->full_name) }}')" style="background:#fef2f2; border:1px solid #ef4444; color:#b91c1c; border-radius:999px; padding:.2rem .75rem; font-size:.68rem; font-weight:800; cursor:pointer;" title="Click to enable account">
                                        ● Disabled
                                    </button>
                                @else
                                    <button onclick="openDisableModal({{ $u->id }}, '{{ addslashes($u->full_name) }}')" style="background:#f0fdf4; border:1px solid #22c55e; color:#15803d; border-radius:999px; padding:.2rem .75rem; font-size:.68rem; font-weight:800; cursor:pointer;" title="Click to disable account">
                                        ● Active
                                    </button>
                                @endif
                            </td>
                            <td style="color:#64748b; font-size:.78rem;">
                                {{ $u->last_login ? \Carbon\Carbon::parse($u->last_login)->format('M d, Y h:i A') : 'Never' }}
                            </td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <button class="p-2 text-slate-400 hover:text-amber-600 transition-colors" title="Edit User" onclick="openEditUserModal({{ json_encode($editData) }})">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button class="p-2 text-slate-400 hover:text-rose-600 transition-colors" title="Archive User" onclick="archiveUser({{ $u->id }}, '{{ addslashes($u->full_name) }}')">
                                        <i data-lucide="archive" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
    </div>

    {{-- ─── PAGE ACCESS TAB ─── --}}
        <div id="tab-access" class="sa-tab-content {{ $tab === 'access' ? '' : 'hidden' }}">
            <div style="background:#f0fdfa; border:1px solid #ccfbf1; border-radius:1.5rem; padding:.85rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:.75rem;">
                <i data-lucide="info" style="width:15px;height:15px;color:#14b8a6;flex-shrink:0;"></i>
                <p style="font-size:.78rem; color:#0f766e; font-weight:600;">Click a user below, then toggle which pages they can access. If nothing is selected, the user will have NO access to restricted pages.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- User Picker --}}
                <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                    <div style="padding:.85rem 1rem; border-bottom:1px solid var(--sa-border); font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#64748b;">
                        Select User
                    </div>
                    <div style="max-height:460px; overflow-y:auto;">
                        @foreach($allUsers->where('approval_status', 'approved') as $u)
                        <div class="access-user-item" data-id="{{ $u->id }}" data-allowed="{{ json_encode($u->allowed_pages ?? null) }}"
                             onclick="selectAccessUser(this)"
                             style="padding:.85rem 1rem; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:.75rem; transition:background .15s; margin: .25rem; border-radius: .75rem;">
                            <div style="width:32px;height:32px;background:#e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.75rem;color:#64748b;flex-shrink:0;">
                                {{ strtoupper(substr($u->full_name ?? 'U', 0, 1)) }}
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:700;color:#000;font-size:.82rem; truncate;">{{ $u->full_name }}</div>
                                <div style="font-size:.68rem;color:#64748b;">{{ $u->role === 'super_admin' ? 'Owner' : ucfirst($u->role) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Page Picker --}}
                <div class="lg:col-span-2" style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                    <div style="padding:.85rem 1rem; border-bottom:1px solid var(--sa-border); display:flex; align-items:center; justify-between;">
                        <div>
                            <span style="font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#64748b;">Page Permissions</span>
                            <span id="access-user-name" style="font-size:.8rem; color:#f59e0b; font-weight:700; margin-left:.6rem;"></span>
                        </div>
                        <div class="flex gap-2 ml-auto">
                            <button class="btn-ghost" onclick="selectAllPages()" style="font-size:.68rem;">Select All</button>
                            <button class="btn-ghost" onclick="clearAllPages()" style="font-size:.68rem;">Clear All</button>
                            <button class="btn-gold" onclick="savePageAccess()" id="save-access-btn" disabled style="opacity:.4; font-size:.72rem;">
                                <i data-lucide="save" class="inline w-3 h-3 mr-1"></i>Save Access
                            </button>
                        </div>
                    </div>
                    <div id="page-picker-body" style="padding:1.25rem;">
                        <div style="text-align:center; color:#64748b; padding:2rem;" id="access-placeholder">
                            <i data-lucide="mouse-pointer-click" style="width:36px;height:36px;margin:0 auto .75rem;display:block;opacity:.4;"></i>
                            <p style="font-size:.82rem;">Select a user on the left to manage their page access.</p>
                        </div>
                        <div id="page-chips-container" class="hidden">
                            @foreach($groups as $groupName => $groupPages)
                            <div class="mb-4">
                                <p style="font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:#4b5563; margin-bottom:.6rem;">{{ $groupName }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($groupPages as $routePattern => $pageDef)
                                    <button class="page-chip" data-route="{{ $routePattern }}" onclick="togglePageChip(this)">
                                        <i data-lucide="{{ $pageDef['icon'] }}" class="inline" style="width:10px;height:10px;margin-right:.2rem;"></i>
                                        {{ $pageDef['label'] }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                            <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--sa-border); font-size:.72rem; color:#64748b;">
                                <i data-lucide="info" class="inline w-3 h-3 mr-1"></i>
                                <strong style="color:#b91c1c;">No chips selected</strong> = Restricted Access. The user will not be able to view any pages until permissions are granted.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── AUDIT LOG TAB ─── --}}
        <div id="tab-audit" class="sa-tab-content {{ $tab === 'audit' ? '' : 'hidden' }}">
            {{-- Filters --}}
            <div class="flex flex-wrap gap-3 mb-5">
                <div class="relative flex-1" style="max-width: 320px;">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" id="auditSearch" class="sa-input pl-10" placeholder="Search logs by name, email, or notes..." autocomplete="off" oninput="debouncedAuditLog()">
                </div>
                <select id="auditActionFilter" class="sa-input" style="max-width:160px;" onchange="loadAuditLog(1)">
                    <option value="">All Actions</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="failed_login">Failed Login</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="password_changed">Password Changed</option>
                    <option value="created">Account Created</option>
                </select>
                <select id="auditRoleFilter" class="sa-input" style="max-width:160px;" onchange="loadAuditLog(1)">
                    <option value="">All Roles</option>
                    <option value="dispatcher">Dispatcher</option>
                    <option value="manager">Manager</option>
                    <option value="secretary">Secretary</option>
                    <option value="super_admin">Owner</option>
                </select>
            </div>

            <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                <div id="audit-table-container" style="overflow-x:auto; max-height: 700px; overflow-y: auto;">
                    <table class="sa-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Browser / Device</th>
                                <th>Notes</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody id="audit-tbody">
                            @foreach($auditLog as $a)
                            <tr>
                                <td>
                                    <div style="font-weight:700;color:#000;font-size:.82rem;">{{ $a->user_name ?? 'Unknown' }}</div>
                                    <div style="font-size:.7rem;color:#64748b;">{{ $a->user_email ?? '—' }}</div>
                                </td>
                                <td>
                                    @if($a->user_role)
                                        <span class="badge badge-role-{{ $a->user_role }}">{{ $a->user_role === 'super_admin' ? 'Owner' : ucfirst(str_replace('_',' ',$a->user_role)) }}</span>
                                    @else
                                        <span style="color:#64748b;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $aMap2 = [
                                            'login'=>['badge-login','● Login'],
                                            'logout'=>['badge-logout','○ Logout'],
                                            'failed_login'=>['badge-failed','✕ Failed'],
                                            'approved'=>['badge-approved','✔ Approved'],
                                            'rejected'=>['badge-rejected','✕ Rejected'],
                                            'password_changed'=>['badge-role-manager','★ PW Changed'],
                                            'created'=>['badge-login','★ Created']
                                        ];
                                        [$cls2,$lbl2] = $aMap2[$a->action] ?? ['badge-logout',$a->action];
                                    @endphp
                                    <span class="badge {{ $cls2 }}">{{ $lbl2 }}</span>
                                </td>
                                <td style="color:#64748b;font-family:monospace;font-size:.76rem;">{{ $a->ip_address ?? '—' }}</td>
                                <td style="color:#64748b;font-size:.72rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $a->user_agent }}">
                                    {{ Str::limit($a->user_agent ?? '—', 45) }}
                                </td>
                                <td style="color:#64748b;font-size:.75rem;">{{ $a->notes ?? '—' }}</td>
                                <td style="color:#64748b;font-size:.75rem;white-space:nowrap;">{{ \Carbon\Carbon::parse($a->created_at)->format('M d, Y h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="flex items-center justify-between px-4 py-3 border-t border-sa-border" style="border-color:var(--sa-border);">
                    <span id="audit-info" style="font-size:.75rem; color:#64748b;">Showing {{ $auditLog->firstItem() ?? 0 }} – {{ $auditLog->lastItem() ?? 0 }} of {{ $auditLog->total() }}</span>
                    <div class="flex gap-2" id="audit-pagination">
                        @if($auditLog->onFirstPage())
                            <button class="btn-ghost" disabled style="opacity:.4; cursor:not-allowed;">← Prev</button>
                        @else
                            <button class="btn-ghost" onclick="loadAuditLog({{ $auditLog->currentPage() - 1 }})">← Prev</button>
                        @endif
                        @if($auditLog->hasMorePages())
                            <button class="btn-ghost" onclick="loadAuditLog({{ $auditLog->currentPage() + 1 }})">Next →</button>
                        @else
                            <button class="btn-ghost" disabled style="opacity:.4; cursor:not-allowed;">Next →</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        </div>

        {{-- ─── SECURITY TAB ─── --}}
        <div id="tab-security" class="sa-tab-content {{ $tab === 'security' ? '' : 'hidden' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Archive Protection Card --}}
                <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                    <div style="padding:1.5rem; border-bottom:1px solid var(--sa-border); background:#fef2f2; display:flex; align-items:center; gap:.75rem;">
                        <div class="w-10 h-10 bg-white text-rose-600 rounded-xl flex items-center justify-center shadow-sm">
                            <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span style="font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#991b1b;">Archive Deletion Lock</span>
                            <p style="color:#b91c1c; font-size:.65rem; font-weight:600; margin-top:1px;">Prevents accidental or unauthorized permanent data loss.</p>
                        </div>
                    </div>
                    <div style="padding:1.5rem;">
                        <form id="archive-password-form" onsubmit="updateArchivePassword(event)">
                            <div class="mb-4">
                                <label class="sa-label">Current Deletion Password</label>
                                <div style="font-size:.7rem; color:#64748b; margin-bottom:.5rem;">If this is your first time, leave the password field empty (or use default).</div>
                                <div class="relative">
                                    <input type="password" name="archive_password" id="sec-archive-pwd" class="sa-input" placeholder="Enter new deletion password" required minlength="6">
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="sa-label">Confirm New Password</label>
                                <input type="password" name="archive_password_confirmation" id="sec-archive-pwd-confirm" class="sa-input" placeholder="Repeat deletion password" required>
                            </div>
                            
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:1rem; padding:1rem; margin-bottom:1.5rem;">
                                <div class="flex gap-2 text-blue-600 mb-1.5">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                    <span style="font-size:.75rem; font-weight:800; text-transform:uppercase;">Security Notice</span>
                                </div>
                                <p style="font-size:.72rem; color:#475569; line-height:1.5;">This password is **separate** from your login password. It is required whenever someone attempts to **Permanently Delete** items from the archives (Users, Roles, Incident Types).</p>
                            </div>

                            <button type="submit" class="btn-gold w-full py-3 flex items-center justify-center gap-2" style="background:#1c1917;">
                                <i data-lucide="check-circle" class="w-4 h-4"></i> Update Deletion Password
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Additional Security Info --}}
                <div class="flex flex-col gap-6">
                    <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; padding:1.5rem;">
                         <h4 style="font-weight:900; font-size:1rem; color:#000; margin-bottom:1rem;">System Integrity Status</h4>
                         <div class="space-y-4">
                             <div class="flex items-center justify-between py-2 border-bottom border-slate-50">
                                 <div class="flex items-center gap-3">
                                     <i data-lucide="database" class="w-4 h-4 text-emerald-500"></i>
                                     <span class="text-sm font-bold text-slate-700">Database Connection</span>
                                 </div>
                                 <span class="badge badge-approved">SECURE</span>
                             </div>
                             <div class="flex items-center justify-between py-2 border-bottom border-slate-50">
                                 <div class="flex items-center gap-3">
                                     <i data-lucide="shield-check" class="w-4 h-4 text-amber-500"></i>
                                     <span class="text-sm font-bold text-slate-700">MFA Enforcement</span>
                                 </div>
                                 <span class="badge badge-approved">ACTIVE</span>
                             </div>
                             <div class="flex items-center justify-between py-2">
                                 <div class="flex items-center gap-3">
                                     <i data-lucide="history" class="w-4 h-4 text-blue-500"></i>
                                     <span class="text-sm font-bold text-slate-700">Audit Logging</span>
                                 </div>
                                 <span class="badge badge-approved">LOGGING</span>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
</div>

{{-- Toast --}}
<div id="sa-toast"></div>

{{-- User Details & Activity Modal --}}
<div class="sa-modal-backdrop" id="userDetailsModal">
    <div class="sa-modal" style="max-width: 650px; padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        
        {{-- Header --}}
        <div style="background: #f8fafc; padding: 1.5rem; border-bottom: 1px solid var(--sa-border); position: relative;">
            <button onclick="closeUserDetailsModal()" style="position: absolute; top: 1rem; right: 1rem; color: #64748b; cursor: pointer; padding: .4rem; transition: color .2s;" class="hover:text-black">
                <i data-lucide="x" style="width:20px;height:20px;"></i>
            </button>
            
            <div class="flex items-center gap-4">
                <div id="ud-avatar" style="width:56px;height:56px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.2rem;color:#475569;border:2px solid #fcd34d;flex-shrink:0;">
                    U
                </div>
                <div>
                    <h3 id="ud-name" style="color: #000; font-weight: 900; font-size: 1.25rem; margin-bottom: .2rem;">Loading...</h3>
                    <p id="ud-email" style="color: #64748b; font-size: .8rem; margin-bottom: .4rem;">—</p>
                    <div class="flex gap-2" id="ud-badges">
                        {{-- Badges populated by JS --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions Bar --}}
        <div style="padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid var(--sa-border); display: flex; gap: .75rem; justify-content: flex-end;">
            <button class="btn-ghost" id="ud-btn-role" onclick="" style="font-size: .75rem;">
                <i data-lucide="shield" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>Change Role
            </button>
            <button class="btn-ghost" id="ud-btn-pw" onclick="" style="font-size: .75rem;">
                <i data-lucide="key" class="inline w-3.5 h-3.5 mr-1 -mt-0.5"></i>Reset Password
            </button>
        </div>

        {{-- Activity Timeline --}}
        <div style="padding: 1.5rem; overflow-y: auto; max-height: 400px; background: #fafafa;">
            <h4 style="font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin-bottom: 1rem;">
                <i data-lucide="activity" class="inline w-3.5 h-3.5 mr-1 -mt-0.5 text-amber-500"></i>Activity Timeline
            </h4>
            
            <div id="ud-timeline" style="position: relative; padding-left: 1rem;">
                {{-- Timeline items populated by JS --}}
                <div style="color: #64748b; font-size: .8rem; text-align: center; padding: 2rem;">Loading activity history...</div>
            </div>
        </div>

    </div>
</div>

{{-- Password Reset Modal --}}
<div class="sa-modal-backdrop" id="pwModal">
    <div class="sa-modal">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 style="color:#000; font-weight:900; font-size:1.05rem;">Reset Password</h3>
                <p id="pw-modal-name" style="color:#64748b; font-size:.8rem; margin-top:.2rem;"></p>
            </div>
            <button onclick="closePwModal()" style="color:#64748b; cursor:pointer; padding:.4rem;"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        </div>
        <input type="hidden" id="pw-user-id">
        <div class="mb-4">
            <label style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; display:block; margin-bottom:.5rem;">New Password</label>
            <input type="password" id="pw-new" class="sa-input" placeholder="Minimum 6 characters">
        </div>
        <div class="mb-5">
            <label style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; display:block; margin-bottom:.5rem;">Confirm Password</label>
            <input type="password" id="pw-confirm" class="sa-input" placeholder="Repeat new password">
        </div>
        <div class="flex gap-3 justify-end">
            <button class="btn-ghost" onclick="closePwModal()">Cancel</button>
            <button class="btn-gold" onclick="submitPasswordReset()">
                <i data-lucide="key" class="inline w-3 h-3 mr-1"></i>Reset Password
            </button>
        </div>
    </div>
</div>

{{-- Role Update Modal --}}
<div class="sa-modal-backdrop" id="roleModal">
    <div class="sa-modal">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 style="color:#000; font-weight:900; font-size:1.05rem;">Update User Role</h3>
                <p id="role-modal-name" style="color:#64748b; font-size:.8rem; margin-top:.2rem;"></p>
            </div>
            <button onclick="closeRoleModal()" style="color:#64748b; cursor:pointer; padding:.4rem;"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        </div>
        <input type="hidden" id="role-user-id">
        <div class="mb-6">
            <label style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; display:block; margin-bottom:.5rem;">Assign New Role</label>
            <select id="role-select" class="sa-input">
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->label }}</option>
                @endforeach
            </select>
            <p style="font-size:.68rem; color:#64748b; margin-top:.75rem;">Changing a role will grant the user the default permissions for that role unless specific page access is set below.</p>
        </div>
        <div class="flex gap-3 justify-end">
            <button class="btn-ghost" onclick="closeRoleModal()">Cancel</button>
            <button class="btn-gold" onclick="submitRoleUpdate()" style="background:#f59e0b; color:black;">
                <i data-lucide="shield" class="inline w-3 h-3 mr-1"></i>Update Role
            </button>
        </div>
    </div>
</div>

{{-- Classification Management Modal --}}
 <div class="sa-modal-backdrop" id="classificationModal">
    <div class="sa-modal" style="max-width: 450px;">
        <div class="flex items-center justify-between mb-6">
            <h3 id="cls-modal-title" style="font-weight:900; font-size:1.2rem; color:#000;">Add Classification</h3>
            <button class="btn-ghost" onclick="document.getElementById('classificationModal').classList.remove('active')">✕</button>
        </div>
        <form id="cls-form" onsubmit="submitClassification(event)">
            <input type="hidden" id="cls-id">
            <div class="mb-4">
                <label class="sa-label">Classification Name</label>
                <input type="text" id="cls-name" class="sa-input" placeholder="e.g. Traffic Violation" required>
            </div>
            <div class="mb-4">
                <label class="sa-label">Default Severity</label>
                <select id="cls-severity" class="sa-input" required>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="sa-label">Theme Color</label>
                    <input type="text" id="cls-color" class="sa-input" placeholder="#hex or name" required>
                </div>
                <div>
                    <label class="sa-label">Lucide Icon</label>
                    <input type="text" id="cls-icon" class="sa-input" placeholder="e.g. alert-circle" required>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-gold flex-1 py-3">Save Classification</button>
                <button type="button" class="btn-ghost flex-1 py-3" onclick="document.getElementById('classificationModal').classList.remove('active')">Cancel</button>
            </div>
        </form>
    </div>
 </div>

 {{-- Manage Roles List Modal --}}
 <div class="sa-modal-backdrop" id="manageRolesModal">
    <div class="sa-modal" style="max-width: 950px; padding: 0; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
        {{-- Header --}}
        <div style="background: #f8fafc; padding: 1.5rem; border-bottom: 1px solid var(--sa-border); position: relative;">
            <button onclick="document.getElementById('manageRolesModal').classList.remove('open')" style="position: absolute; top: 1.25rem; right: 1.25rem; color: #64748b; cursor: pointer; padding: .4rem; transition: color .2s;" class="hover:text-black">
                <i data-lucide="x" style="width:20px;height:20px;"></i>
            </button>
            <div class="flex items-center justify-between pr-10">
                <div>
                    <h3 style="color:#000; font-weight:900; font-size:1.4rem; margin-bottom:.2rem;">System Role Management</h3>
                    <p style="color:#64748b; font-size:.85rem;">Define, modify, or retire specialized access roles for your organization.</p>
                </div>
                <button class="btn-gold px-6 py-2.5 flex items-center gap-2" onclick="openAddRoleModal()">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add New Role
                </button>
            </div>
        </div>
        
        {{-- Content --}}
        <div style="padding: 1.5rem; overflow-y: auto; flex: 1; background: #fff;">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Active Roles --}}
                <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden;">
                    <div style="padding:1.25rem; border-bottom:1px solid var(--sa-border); background:#f8fafc; display:flex; align-items:center; gap:.75rem;">
                        <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <span style="font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#475569;">Active System Roles</span>
                    </div>
                    <div style="padding:1rem;">
                        <div class="space-y-3">
                            @foreach($roles as $r)
                            <div class="group border border-slate-100 rounded-xl p-4 hover:border-amber-200 hover:bg-amber-50/20 transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                                            <i data-lucide="user-cog" class="w-5 h-5 text-slate-400 group-hover:text-amber-600"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h5 class="text-sm font-black text-slate-900">{{ $r->label }}</h5>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">{{ $r->name }}</span>
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $r->description ?? 'No description provided.' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button class="p-2 hover:text-amber-600 transition-colors" title="Edit Role" onclick="editRole({{ json_encode($r) }})">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button class="p-2 hover:text-rose-600 transition-colors" title="Archive Role" onclick="archiveRole({{ $r->id }})">
                                            <i data-lucide="archive" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Archived Roles --}}
                <div style="background:var(--sa-card); border:1px solid var(--sa-border); border-radius:1.5rem; overflow:hidden; opacity: 0.8;">
                    <div style="padding:1.25rem; border-bottom:1px solid var(--sa-border); background:#f8fafc; display:flex; align-items:center; gap:.75rem;">
                        <div class="w-8 h-8 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="archive" class="w-4 h-4"></i>
                        </div>
                        <span style="font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#475569;">Archived / Retired Roles</span>
                    </div>
                    <div style="padding:1rem;">
                        @if($archivedRoles->count() > 0)
                            <div class="space-y-3">
                                @foreach($archivedRoles as $r)
                                <div class="group border border-slate-100 rounded-xl p-4 bg-slate-50/50 grayscale">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-slate-200 rounded-xl flex items-center justify-center">
                                                <i data-lucide="user-minus" class="w-5 h-5 text-slate-400"></i>
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-bold text-slate-600">{{ $r->label }}</h5>
                                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $r->name }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button class="p-2 hover:text-emerald-600 transition-colors" title="Restore Role" onclick="restoreRole({{ $r->id }})">
                                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                            </button>
                                            <button class="p-2 hover:text-rose-600 transition-colors" title="Delete Permanently" onclick="deleteRole({{ $r->id }})">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <i data-lucide="archive" class="w-12 h-12 text-slate-200 mx-auto mb-3"></i>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">No Archived Roles</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--sa-border); background: #f8fafc; text-align: right;">
            <button class="btn-ghost px-8 py-2" onclick="document.getElementById('manageRolesModal').classList.remove('open')">Close Manager</button>
        </div>
    </div>
 </div>


 {{-- Role Detail Modal --}}
 <div class="sa-modal-backdrop" id="roleDetailModal">
    <div class="sa-modal" style="max-width: 450px;">
        <div class="flex items-center justify-between mb-6">
            <h3 id="role-detail-title" style="font-weight:900; font-size:1.2rem; color:#000;">Add System Role</h3>
            <button class="btn-ghost" onclick="document.getElementById('roleDetailModal').classList.remove('open')">✕</button>
        </div>
        <form id="role-detail-form" onsubmit="submitRoleDetail(event)">
            <input type="hidden" id="role-detail-id">
            <div class="mb-4">
                <label style="font-size:.72rem; font-weight:800; text-transform:uppercase; color:#64748b; display:block; margin-bottom:.5rem;">Role Name (Slug)</label>
                <input type="text" id="role-detail-name" class="sa-input" placeholder="e.g. cashier" required>
                <p class="text-[10px] text-slate-400 mt-1">Unique identifier used by the system (lowercase, no spaces).</p>
            </div>
            <div class="mb-4">
                <label style="font-size:.72rem; font-weight:800; text-transform:uppercase; color:#64748b; display:block; margin-bottom:.5rem;">Display Label</label>
                <input type="text" id="role-detail-label" class="sa-input" placeholder="e.g. Head Cashier" required>
            </div>
            <div class="mb-6">
                <label style="font-size:.72rem; font-weight:800; text-transform:uppercase; color:#64748b; display:block; margin-bottom:.5rem;">Description</label>
                <textarea id="role-detail-desc" class="sa-input" rows="3" placeholder="Briefly describe what this role does..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-gold flex-1 py-3">Save System Role</button>
                <button type="button" class="btn-ghost flex-1 py-3" onclick="document.getElementById('roleDetailModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
 </div>

  {{-- Edit User Modal --}}
 <div class="sa-modal-backdrop" id="editUserModal">
    <div class="sa-modal" style="max-width: 500px;">
        <div class="flex items-center justify-between mb-6">
            <h3 style="font-weight:900; font-size:1.2rem; color:#000;">Edit User Account</h3>
            <button class="btn-ghost" onclick="document.getElementById('editUserModal').classList.remove('open')">✕</button>
        </div>
        <form id="edit-user-form" onsubmit="submitUserEdit(event)">
            <input type="hidden" id="edit-user-id">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="sa-label">First Name</label>
                    <input type="text" id="edit-first-name" class="sa-input" required>
                </div>
                <div>
                    <label class="sa-label">Last Name</label>
                    <input type="text" id="edit-last-name" class="sa-input" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="sa-label">Email Address</label>
                <input type="email" id="edit-email" class="sa-input" required>
            </div>
            <div class="mb-4">
                <label class="sa-label">Assign Role</label>
                <select id="edit-role" class="sa-input" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ $r->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="sa-label">Phone Number</label>
                <input type="text" id="edit-phone" class="sa-input">
            </div>
            <div class="mb-6">
                <label class="sa-label">Home Address</label>
                <textarea id="edit-address" class="sa-input" rows="2"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-gold flex-1 py-3">Update Account</button>
                <button type="button" class="btn-ghost flex-1 py-3" onclick="document.getElementById('editUserModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
 </div>

 {{-- User Archives Modal --}}
 <div class="sa-modal-backdrop" id="archivesModal">
    <div class="sa-modal" style="max-width: 800px; padding: 0; overflow: hidden; display: flex; flex-direction: column; max-height: 85vh;">
        <div style="background: #f8fafc; padding: 1.5rem; border-bottom: 1px solid var(--sa-border); display: flex; items-center justify-between;">
            <div>
                <h3 style="font-weight:900; font-size:1.2rem; color:#000;">User Archives</h3>
                <p style="color:#64748b; font-size:.75rem;">Previously deleted staff accounts. You can restore them if needed.</p>
            </div>
            <button class="btn-ghost" onclick="document.getElementById('archivesModal').classList.remove('open')">✕</button>
        </div>
        
        <div style="padding: 1rem; overflow-y: auto; flex: 1; background: #fff;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Date Deleted</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $archivedCount = 0; @endphp
                    @foreach($allUsers->whereNotNull('deleted_at') as $u)
                        @php $archivedCount++; @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700;color:#000;">{{ $u->full_name }}</div>
                                <div style="font-size:.7rem;color:#64748b;">{{ $u->email }}</div>
                            </td>
                            <td><span class="badge badge-role-{{ $u->role }}">{{ $u->role === 'super_admin' ? 'Owner' : ucfirst(str_replace('_', ' ', $u->role)) }}</span></td>
                            <td style="color:#64748b; font-size:.75rem;">{{ $u->deleted_at->format('M d, Y h:i A') }}</td>
                            <td style="text-align:right;">
                                <div class="flex gap-2 justify-end">
                                    <button class="btn-approve px-4 py-1.5" onclick="restoreUser({{ $u->id }}, '{{ $u->full_name }}')">
                                        <i data-lucide="rotate-ccw" class="inline w-3 h-3 mr-1"></i> Restore
                                    </button>
                                    <button class="btn-reject px-4 py-1.5" onclick="deleteUserPermanently({{ $u->id }}, '{{ $u->full_name }}')">
                                        <i data-lucide="trash-2" class="inline w-3 h-3 mr-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($archivedCount === 0)
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 3rem; color: #64748b;">
                                <i data-lucide="archive" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                No archived users found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem; border-top: 1px solid var(--sa-border); background: #f8fafc; text-align: right;">
            <button class="btn-ghost px-6" onclick="document.getElementById('archivesModal').classList.remove('open')">Close</button>
        </div>
    </div>
 </div>

  {{-- Archive Deletion Security Modal --}}
  <div class="sa-modal-backdrop" id="archiveSecurityModal">
     <div class="sa-modal" style="max-width: 420px; text-align: center;">
         <div style="background: #fef2f2; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 4px solid #fee2e2;">
             <i data-lucide="shield-alert" style="width: 32px; height: 32px; color: #dc2626;"></i>
         </div>
         
         <h3 style="font-weight: 900; font-size: 1.25rem; color: #991b1b; margin-bottom: .5rem;">Security Verification</h3>
         <p style="color: #64748b; font-size: .85rem; margin-bottom: 1.5rem;">To permanently delete this item, please enter the **Archive Deletion Password** below.</p>
         
         <div class="mb-6">
             <input type="password" id="archive-security-pwd" class="sa-input" style="text-align: center; font-size: 1.2rem; letter-spacing: .2em;" placeholder="••••••">
         </div>
         
         <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: .75rem; padding: .75rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: .75rem; text-align: left;">
             <i data-lucide="alert-triangle" style="width: 16px; height: 16px; color: #d97706; flex-shrink: 0; margin-top: 2px;"></i>
             <p style="font-size: .7rem; color: #92400e; font-weight: 500;">Warning: Permanent deletion is **irreversible**. All associated data will be removed from the system forever.</p>
         </div>

         <div class="flex gap-3">
             <button class="btn-ghost flex-1 py-3" onclick="document.getElementById('archiveSecurityModal').classList.remove('open')">Cancel</button>
             <button class="btn-danger flex-1 py-3" id="btn-confirm-permanent-delete">Confirm Delete</button>
         </div>
     </div>
  </div>

 {{-- Disable User Modal --}}
 <div class="sa-modal-backdrop" id="disableUserModal">
    <div class="sa-modal" style="max-width: 450px;">
        <div class="flex items-center justify-between mb-6">
            <h3 style="font-weight:900; font-size:1.2rem; color:#000;">Disable Account</h3>
            <button class="btn-ghost" onclick="document.getElementById('disableUserModal').classList.remove('open')">✕</button>
        </div>
        <div style="background:#fff7ed; border:1px solid #ffedd5; border-radius:1rem; padding:1rem; margin-bottom:1.5rem; display:flex; gap:.75rem;">
            <i data-lucide="alert-triangle" style="width:18px;height:18px;color:#f59e0b;flex-shrink:0;"></i>
            <p style="font-size:.78rem; color:#9a3412; font-weight:600;">Disabling <strong id="disable-user-display-name"></strong>'s account will automatically log them out and block future access until re-enabled.</p>
        </div>
        <form id="disable-user-form" onsubmit="submitDisable(event)">
            <input type="hidden" id="disable-user-id">
            <div class="mb-6">
                <label class="sa-label">Reason for disabling (will be shown to user)</label>
                <textarea id="disable-reason" class="sa-input" rows="3" placeholder="e.g. Account security under review, Overdue payments, etc." required></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-danger flex-1 py-3" style="background:#ef4444; color:#fff; border:none; font-weight:800;">Confirm Disable</button>
                <button type="button" class="btn-ghost flex-1 py-3" onclick="document.getElementById('disableUserModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
 </div>




<script>
// Use var to allow re-declaration during AJAX navigation
var csrfMeta = document.querySelector('meta[name="csrf-token"]');
var CSRF = csrfMeta ? csrfMeta.content : '';

// ─── User Details Modal ────────────────────────────────────────────────────────
async function openUserDetailsModal(id) {
    const modal = document.getElementById('userDetailsModal');
    modal.classList.add('open');
    
    // Reset contents
    document.getElementById('ud-name').textContent = 'Loading...';
    document.getElementById('ud-email').textContent = '—';
    document.getElementById('ud-badges').innerHTML = '';
    document.getElementById('ud-timeline').innerHTML = '<div style="color: #64748b; font-size: .8rem; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin mr-2"></i>Loading activity history...</div>';
    
    try {
        const res = await fetch(`/super-admin/users/${id}/details`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        
        if (data.success) {
            renderUserDetails(data.user, data.history);
        } else {
            toast('Failed to load user details.', true);
            closeUserDetailsModal();
        }
    } catch (e) {
        toast('Network error while loading user details.', true);
        closeUserDetailsModal();
    }
}

function closeUserDetailsModal() {
    document.getElementById('userDetailsModal').classList.remove('open');
}

function renderUserDetails(user, history) {
    // 1. Avatar
    const avatar = document.getElementById('ud-avatar');
    if (user.profile_url) {
        avatar.innerHTML = `<img src="${user.profile_url}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="Avatar">`;
    } else {
        avatar.innerHTML = user.initials;
    }

    // 2. Info
    document.getElementById('ud-name').textContent = user.full_name;
    document.getElementById('ud-email').textContent = user.email + (user.phone_number ? ' · ' + user.phone_number : '');
    
    // 3. Badges
    const isActivated = !user.must_change_password && user.last_login;
    const roleMap = {
        'manager': 'badge-role-manager',
        'dispatcher': 'badge-role-dispatcher',
        'secretary': 'badge-role-secretary',
        'staff': 'badge-role-staff',
        'super_admin': 'badge-role-super_admin'
    };
    const roleClass = roleMap[user.role] || 'badge-role-staff';
    const statusBadge = user.trashed 
        ? '<span class="badge badge-rejected">Archived</span>' 
        : (isActivated ? '<span class="badge badge-approved">Activated</span>' : '<span class="badge badge-pending">Pending</span>');
        
    const activeBadge = user.is_disabled
        ? '<span style="color:#b91c1c;font-size:.65rem;font-weight:700;border:1px solid #fca5a5;padding:.1rem .5rem;border-radius:99px;background:#fef2f2;">● Disabled</span>'
        : '<span style="color:#15803d;font-size:.65rem;font-weight:700;border:1px solid #86efac;padding:.1rem .5rem;border-radius:99px;background:#f0fdf4;">● Active</span>';
        
    document.getElementById('ud-badges').innerHTML = `
        <span class="badge ${roleClass}">${user.role === 'super_admin' ? 'Owner' : user.role.replace('_', ' ')}</span>
        ${statusBadge}
        ${activeBadge}
    `;

    // 4. Actions Binding
    const btnRole = document.getElementById('ud-btn-role');
    const btnPw = document.getElementById('ud-btn-pw');
    
    if (user.trashed || user.role === 'super_admin') {
        btnRole.style.display = 'none';
        btnPw.style.display = 'none';
    } else {
        btnRole.style.display = 'inline-flex';
        btnPw.style.display = 'inline-flex';
        btnRole.onclick = () => openRoleModal(user.id, user.full_name, user.role);
        btnPw.onclick = () => openPasswordModal(user.id, user.full_name);
    }

    // 5. Timeline
    const tl = document.getElementById('ud-timeline');
    if (!history || history.length === 0) {
        tl.innerHTML = '<div style="color: #64748b; font-size: .8rem; text-align: center; padding: 2rem;">No activity history found for this user.</div>';
        return;
    }

    let html = '<div style="position:absolute; left: 1rem; top: .5rem; bottom: 0; width: 2px; background: #e2e8f0; border-radius: 2px;"></div>';
    
    const actionIcons = {
        'login': { color: '#3b82f6', icon: 'log-in' },
        'logout': { color: '#64748b', icon: 'log-out' },
        'failed_login': { color: '#ef4444', icon: 'alert-circle' },
        'approved': { color: '#22c55e', icon: 'check-circle' },
        'rejected': { color: '#f87171', icon: 'x-circle' },
        'password_changed': { color: '#f59e0b', icon: 'key' },
        'created': { color: '#14b8a6', icon: 'user-plus' }
    };

    history.forEach(log => {
        const style = actionIcons[log.action] || { color: '#94a3b8', icon: 'activity' };
        
        // Format date: "Apr 28, 05:07 AM"
        const dateObj = new Date(log.created_at);
        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

        html += `
            <div style="position: relative; padding-left: 2rem; padding-bottom: 1.5rem;">
                <div style="position: absolute; left: -5px; top: 2px; width: 12px; height: 12px; border-radius: 50%; background: #fff; border: 2px solid ${style.color};"></div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: .2rem;">
                    <div style="font-size: .78rem; font-weight: 800; color: #000; text-transform: capitalize;">${log.action.replace('_', ' ')}</div>
                    <div style="font-size: .7rem; color: #64748b; white-space: nowrap; margin-left: 1rem;">${formattedDate}</div>
                </div>
                <div style="font-size: .75rem; color: #475569; line-height: 1.4;">${log.notes || 'No details provided.'}</div>
                ${log.ip_address ? `<div style="font-size: .65rem; color: #64748b; margin-top: .3rem; font-family: monospace;">IP: ${log.ip_address}</div>` : ''}
            </div>
        `;
    });

    tl.innerHTML = html;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ─── Tab Switching ─────────────────────────────────────────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.sa-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.sa-tab').forEach(el => el.classList.remove('active'));
    
    const content = document.getElementById('tab-' + tab);
    if (content) content.classList.remove('hidden');
    
    const tabBtn = document.querySelector(`.sa-tab[onclick*="switchTab('${tab}')"]`);
    if (tabBtn) tabBtn.classList.add('active');
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ─── Toast ────────────────────────────────────────────────────────────────────
function toast(msg, isError = false) {
    const el = document.getElementById('sa-toast');
    if (!el) return;
    
    const icon = isError ? 'alert-circle' : 'check-circle';
    const iconColor = isError ? '#ef4444' : '#10b981';
    
    el.innerHTML = `
        <i data-lucide="${icon}" style="width:18px;height:18px;color:${iconColor}; flex-shrink:0;"></i>
        <span style="white-space: nowrap;">${msg}</span>
    `;
    
    if (window.lucide) window.lucide.createIcons();
    
    el.className = 'show' + (isError ? ' error' : '');
    setTimeout(() => el.className = '', 3500);
}

// ─── Approve / Reject (Left here for API backwards compatibility) ────
async function approveUser(id, name) {
    if (!confirm('Approve account for ' + name + '? They will be able to log in immediately.')) return;
    const res = await fetch(`/super-admin/approve/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
        toast(data.message);
        setTimeout(() => location.reload(), 1500);
    } else {
        toast(data.message || 'Error.', true);
    }
}

async function rejectUser(id, name) {
    if (!confirm('Reject account for ' + name + '?')) return;
    const res = await fetch(`/super-admin/reject/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
        toast('Rejected: ' + name);
        setTimeout(() => location.reload(), 1500);
    } else {
        toast(data.message || 'Error.', true);
    }
}

// ─── Disable / Enable ─────────────────────────────────────────────────────────
function openDisableModal(id, name) {
    document.getElementById('disable-user-id').value = id;
    document.getElementById('disable-user-display-name').textContent = name;
    document.getElementById('disable-reason').value = '';
    document.getElementById('disableUserModal').classList.add('open');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

async function submitDisable(e) {
    e.preventDefault();
    const id = document.getElementById('disable-user-id').value;
    const reason = document.getElementById('disable-reason').value;

    try {
        const res = await fetch(`/super-admin/toggle-disable/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ is_disabled: true, reason })
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message);
            location.reload();
        } else {
            toast(data.message || 'Error.', true);
        }
    } catch (e) { toast('Network error.', true); }
}

async function confirmEnable(id, name) {
    if (!confirm(`Re-enable ${name}'s account?`)) return;
    try {
        const res = await fetch(`/super-admin/toggle-disable/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ is_disabled: false })
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message);
            location.reload();
        } else {
            toast(data.message || 'Error.', true);
        }
    } catch (e) { toast('Network error.', true); }
}

// ─── Archive / Restore ─────────────────────────────────────────────────────────
async function archiveUser(id, name) {
    if (!confirm(`Move ${name} to archives? They will be unable to log in.`)) return;
    try {
        const res = await fetch(`/super-admin/users/${id}/archive`, { 
            method: 'DELETE', 
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } 
        });
        const data = await res.json();
        if (data.success) { 
            toast(data.message); 
            location.reload(); 
        } else {
            toast(data.message || 'Error.', true);
        }
    } catch (e) { toast('Network error.', true); }
}

async function restoreUser(id, name) {
    if (!confirm(`Restore ${name}'s account access?`)) return;
    try {
        const res = await fetch(`/super-admin/users/${id}/restore`, { 
            method: 'POST', 
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } 
        });
        const data = await res.json();
        if (data.success) { 
            toast(data.message); 
            location.reload(); 
        } else {
            toast(data.message || 'Error.', true);
        }
    } catch (e) { toast('Network error.', true); }
}

function openArchivesModal() {
    document.getElementById('archivesModal').classList.add('open');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ─── User Edit ────────────────────────────────────────────────────────────────
function openEditUserModal(user) {
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-first-name').value = user.first_name || '';
    document.getElementById('edit-last-name').value = user.last_name || '';
    document.getElementById('edit-email').value = user.email || '';
    document.getElementById('edit-role').value = user.role || '';
    document.getElementById('edit-phone').value = user.phone_number || '';
    document.getElementById('edit-address').value = user.address || '';
    
    document.getElementById('editUserModal').classList.add('open');
}

async function submitUserEdit(e) {
    e.preventDefault();
    const id = document.getElementById('edit-user-id').value;
    const formData = {
        first_name: document.getElementById('edit-first-name').value,
        last_name: document.getElementById('edit-last-name').value,
        email: document.getElementById('edit-email').value,
        role: document.getElementById('edit-role').value,
        phone_number: document.getElementById('edit-phone').value,
        address: document.getElementById('edit-address').value,
    };

    try {
        const res = await fetch(`/super-admin/users/${id}/update`, {
            method: 'PUT',
            headers: { 
                'X-CSRF-TOKEN': CSRF, 
                'Content-Type': 'application/json',
                'Accept': 'application/json' 
            },
            body: JSON.stringify(formData)
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message);
            location.reload();
        } else {
            toast(data.message || 'Validation error.', true);
        }
    } catch (e) { toast('Network error.', true); }
}

// ─── User Search ──────────────────────────────────────────────────────────────
function filterUserTable(val) {
    val = (val || document.getElementById('userSearch').value || '').toLowerCase();
    const statusVal = document.getElementById('statusFilter').value.toLowerCase();
    document.querySelectorAll('#userTable .user-row').forEach(row => {
        const matchText = row.dataset.name.includes(val) || row.dataset.email.includes(val) || row.dataset.role.includes(val);
        const matchStatus = !statusVal || row.dataset.status === statusVal;
        row.style.display = (matchText && matchStatus) ? '' : 'none';
    });
}

// ─── Page Access ──────────────────────────────────────────────────────────────
var currentAccessUserId = null;

function selectAccessUser(el) {
    document.querySelectorAll('.access-user-item').forEach(i => {
        i.style.background = '';
        i.style.borderLeft = '';
    });
    el.style.background = '#fef3c7';
    el.style.boxShadow = '0 2px 8px rgba(0,0,0,.05)';
    el.style.borderColor = '#f59e0b';

    currentAccessUserId = el.dataset.id;
    document.getElementById('access-user-name').textContent = '— ' + el.querySelector('[style*="font-weight:700"]').textContent.trim();
    document.getElementById('save-access-btn').disabled = false;
    document.getElementById('save-access-btn').style.opacity = '1';
    document.getElementById('access-placeholder').classList.add('hidden');
    document.getElementById('page-chips-container').classList.remove('hidden');

    // Load current allowed pages
    let allowed = null;
    try { allowed = JSON.parse(el.dataset.allowed); } catch(e) {}

    document.querySelectorAll('.page-chip').forEach(chip => {
        const route = chip.dataset.route;
        // If allowed is null → full access (no chips active)
        // If allowed is an array, activate matching chips
        const isActive = allowed && Array.isArray(allowed) && allowed.includes(route);
        chip.classList.toggle('active', isActive);
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function togglePageChip(chip) {
    chip.classList.toggle('active');
}

function selectAllPages() {
    document.querySelectorAll('.page-chip').forEach(c => c.classList.add('active'));
}
function clearAllPages() {
    document.querySelectorAll('.page-chip').forEach(c => c.classList.remove('active'));
}

async function savePageAccess() {
    if (!currentAccessUserId) return;
    const activeChips = [...document.querySelectorAll('.page-chip.active')].map(c => c.dataset.route);
    const pages = activeChips;

    const res = await fetch(`/super-admin/page-access/${currentAccessUserId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ pages })
    });
    const data = await res.json();
    if (data.success) {
        toast(data.message);
        // Update the data attribute on the user item
        const item = document.querySelector(`.access-user-item[data-id="${currentAccessUserId}"]`);
        if (item) item.dataset.allowed = JSON.stringify(pages);
    } else {
        toast(data.message || 'Error saving.', true);
    }
}

// ─── Audit Log Pagination ─────────────────────────────────────────────────────
var auditTimer;
function debouncedAuditLog() {
    clearTimeout(auditTimer);
    auditTimer = setTimeout(() => loadAuditLog(1), 300);
}

async function loadAuditLog(page = 1) {
    const search  = document.getElementById('auditSearch').value;
    const action  = document.getElementById('auditActionFilter').value;
    const role    = document.getElementById('auditRoleFilter').value;
    const params  = new URLSearchParams({ page, search, action, role, per_page: 25 });

    // Visual loading state
    const tbody = document.getElementById('audit-tbody');
    if(page === 1) tbody.style.opacity = '0.5';

    const res  = await fetch(`/super-admin/login-history?${params}`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    tbody.style.opacity = '1';

    const actionMap = {
        login: ['badge-login','● Login'], logout: ['badge-logout','○ Logout'],
        failed_login: ['badge-failed','✕ Failed'], approved: ['badge-approved','✔ Approved'], rejected: ['badge-rejected','✕ Rejected']
    };
    const roleClass = r => `badge-role-${r || 'staff'}`;

    document.getElementById('audit-tbody').innerHTML = data.data.length === 0
        ? `<tr><td colspan="7" style="text-align:center;color:#64748b;padding:2rem;">No records found.</td></tr>`
        : data.data.map(a => {
            const [cls, lbl] = actionMap[a.action] || ['badge-logout', a.action];
            return `<tr>
                <td><div style="font-weight:700;color:#000;font-size:.82rem;">${a.user_name ?? '—'}</div><div style="font-size:.7rem;color:#64748b;">${a.user_email ?? ''}</div></td>
                <td>${a.user_role ? `<span class="badge ${roleClass(a.user_role)}">${a.user_role === 'super_admin' ? 'Owner' : a.user_role.replace('_',' ')}</span>` : '-'}</td>
                <td><span class="badge ${cls}">${lbl}</span></td>
                <td style="color:#64748b;font-family:monospace;font-size:.76rem;">${a.ip_address ?? '—'}</td>
                <td style="color:#64748b;font-size:.72rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${a.user_agent ?? ''}">${(a.user_agent ?? '—').substring(0,50)}</td>
                <td style="color:#475569;font-size:.75rem;">${a.notes ?? '—'}</td>
                <td style="color:#64748b;font-size:.75rem;white-space:nowrap;">${new Date(a.created_at).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
            </tr>`;
        }).join('');

    document.getElementById('audit-info').textContent = `Showing ${data.from ?? 0} – ${data.to ?? 0} of ${data.total}`;

    const pag = document.getElementById('audit-pagination');
    pag.innerHTML = `
        <button class="btn-ghost" onclick="loadAuditLog(${data.current_page - 1})" ${data.current_page <= 1 ? 'disabled style="opacity:.4;cursor:not-allowed;"' : ''}>← Prev</button>
        <button class="btn-ghost" onclick="loadAuditLog(${data.current_page + 1})" ${!data.next_page_url ? 'disabled style="opacity:.4;cursor:not-allowed;"' : ''}>Next →</button>`;

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ─── Password Reset Modal ─────────────────────────────────────────────────────
function openPasswordModal(id, name) {
    document.getElementById('pw-user-id').value = id;
    document.getElementById('pw-modal-name').textContent = 'Resetting password for: ' + name;
    document.getElementById('pw-new').value = '';
    document.getElementById('pw-confirm').value = '';
    document.getElementById('pwModal').classList.add('open');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closePwModal() {
    document.getElementById('pwModal').classList.remove('open');
}
async function submitPasswordReset() {
    const id  = document.getElementById('pw-user-id').value;
    const pw  = document.getElementById('pw-new').value;
    const cpw = document.getElementById('pw-confirm').value;
    if (!pw || pw.length < 6) { toast('Password must be at least 6 characters.', true); return; }
    if (pw !== cpw) { toast('Passwords do not match.', true); return; }

    const res = await fetch(`/super-admin/users/${id}/reset-password`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ password: pw })
    });
    const data = await res.json();
    if (data.success) { toast(data.message); closePwModal(); }
    else toast(data.message || 'Error.', true);
}

// ─── Role Update Modal ──────────────────────────────────────────────────────
function openRoleModal(id, name, currentRole) {
    document.getElementById('role-user-id').value = id;
    document.getElementById('role-modal-name').textContent = 'Promote or change role for: ' + name;
    document.getElementById('role-select').value = currentRole;
    document.getElementById('roleModal').classList.add('open');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closeRoleModal() {
    document.getElementById('roleModal').classList.remove('open');
}
async function submitRoleUpdate() {
    const id   = document.getElementById('role-user-id').value;
    const role = document.getElementById('role-select').value;

    const res = await fetch(`/super-admin/users/${id}/update-role`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ role })
    });
    const data = await res.json();
    if (data.success) {
        toast(data.message);
        closeRoleModal();
        location.reload(); // Reload to see badge update
    } else {
        toast(data.message || 'Error.', true);
    }
}

// ─── Incident Classification Management ────────────────────────────────────
 function openAddClassificationModal() {
     document.getElementById('cls-id').value = '';
     document.getElementById('cls-form').reset();
     document.getElementById('cls-modal-title').textContent = 'Add Incident Classification';
     document.getElementById('classificationModal').classList.add('active');
 }

 function editClassification(c) {
     document.getElementById('cls-id').value = c.id;
     document.getElementById('cls-name').value = c.name;
     document.getElementById('cls-severity').value = c.default_severity;
     document.getElementById('cls-color').value = c.color;
     document.getElementById('cls-icon').value = c.icon;
     document.getElementById('cls-modal-title').textContent = 'Edit Classification';
     document.getElementById('classificationModal').classList.add('active');
 }

async function submitClassification(e) {
     e.preventDefault();
     const id = document.getElementById('cls-id').value;
     const payload = {
         name: document.getElementById('cls-name').value,
         default_severity: document.getElementById('cls-severity').value,
         color: document.getElementById('cls-color').value,
         icon: document.getElementById('cls-icon').value,
     };

     const url = id ? `/super-admin/incident-classifications/${id}` : '/super-admin/incident-classifications';
     
     try {
         const res = await fetch(url, {
             method: id ? 'PATCH' : 'POST',
             headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
             body: JSON.stringify(payload)
         });
         const data = await res.json();
         if (data.success) {
             toast(data.message);
             location.reload();
         } else {
             toast(data.message || 'Error.', true);
         }
     } catch (e) { toast('Error saving.', true); }
 }

 async function archiveClassification(id) {
     if (!confirm('Archive this classification? It will be hidden from the selection list.')) return;
     try {
         const res = await fetch(`/super-admin/incident-classifications/${id}/archive`, {
             method: 'POST',
             headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
         });
         const data = await res.json();
         if (data.success) {
             toast(data.message);
             location.reload();
         } else {
             toast(data.message || 'Error.', true);
         }
     } catch (e) { toast('Error archiving.', true); }
 }

 async function restoreClassification(id) {
     try {
         const res = await fetch(`/super-admin/incident-classifications/${id}/restore`, {
             method: 'POST',
             headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
         });
         const data = await res.json();
         if (data.success) {
             toast(data.message);
             location.reload();
         } else {
             toast(data.message || 'Error.', true);
         }
     } catch (e) { toast('Error restoring.', true); }
 }

 async function deleteClassification(id) {
     promptArchivePassword(async (password) => {
         try {
             const res = await fetch(`/super-admin/incident-classifications/${id}`, {
                 method: 'DELETE',
                 headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                 body: JSON.stringify({ archive_password: password })
             });
             const data = await res.json();
             if (data.success) {
                 toast(data.message);
                 location.reload();
             } else {
                 toast(data.message || 'Error.', true);
             }
         } catch (e) { toast('Error deleting.', true); }
     });
 }

  // ─── Role Management JS ───────────────────────────────────────────────────
  function openManageRolesModal() {
      document.getElementById('manageRolesModal').classList.add('open');
      if (typeof lucide !== 'undefined') lucide.createIcons();
  }

  function openAddRoleModal() {
      document.getElementById('role-detail-id').value = '';
      document.getElementById('role-detail-form').reset();
      document.getElementById('role-detail-title').textContent = 'Add System Role';
      document.getElementById('roleDetailModal').classList.add('open');
  }

  function editRole(r) {
      document.getElementById('role-detail-id').value = r.id;
      document.getElementById('role-detail-name').value = r.name;
      document.getElementById('role-detail-label').value = r.label;
      document.getElementById('role-detail-desc').value = r.description || '';
      document.getElementById('role-detail-title').textContent = 'Edit System Role';
      document.getElementById('roleDetailModal').classList.add('open');
  }

  async function submitRoleDetail(e) {
      e.preventDefault();
      const id = document.getElementById('role-detail-id').value;
      const payload = {
          name: document.getElementById('role-detail-name').value,
          label: document.getElementById('role-detail-label').value,
          description: document.getElementById('role-detail-desc').value,
      };

      const url = id ? `/super-admin/roles/${id}` : '/super-admin/roles';
      const method = id ? 'PUT' : 'POST';
      
      try {
          const res = await fetch(url, {
              method: method,
              headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
              body: JSON.stringify(payload)
          });
          const data = await res.json();
          if (data.success) {
              toast(data.message);
              location.reload();
          } else {
              toast(data.message || 'Error saving role.', true);
          }
      } catch (e) { toast('Error saving role details.', true); }
  }

  async function archiveRole(id) {
      if (!confirm('Archive this role? Existing users with this role will keep it, but you won\'t be able to select it for new staff.')) return;
      try {
          const res = await fetch(`/super-admin/roles/${id}/archive`, {
              method: 'DELETE',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
          });
          const data = await res.json();
          if (data.success) { toast(data.message); location.reload(); }
          else toast(data.message || 'Error.', true);
      } catch (e) { toast('Error archiving role.', true); }
  }

  async function restoreRole(id) {
      try {
          const res = await fetch(`/super-admin/roles/${id}/restore`, {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
          });
          const data = await res.json();
          if (data.success) { toast(data.message); location.reload(); }
          else toast(data.message || 'Error.', true);
      } catch (e) { toast('Error restoring role.', true); }
  }

  async function deleteRole(id) {
      promptArchivePassword(async (password) => {
          try {
              const res = await fetch(`/super-admin/roles/${id}`, {
                  method: 'DELETE',
                  headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                  body: JSON.stringify({ archive_password: password })
              });
              const data = await res.json();
              if (data.success) { toast(data.message); location.reload(); }
              else toast(data.message || 'Error.', true);
          } catch (e) { toast('Error deleting role.', true); }
      });
  }

  // ─── Archive Security Logic ───────────────────────────────────────────────────
  var archiveSecurityCallback = null;

  function promptArchivePassword(callback) {
      archiveSecurityCallback = callback;
      const modal = document.getElementById('archiveSecurityModal');
      document.getElementById('archive-security-pwd').value = '';
      modal.classList.add('open');
      setTimeout(() => document.getElementById('archive-security-pwd').focus(), 300);
  }

  document.getElementById('btn-confirm-permanent-delete').addEventListener('click', async function() {
      const password = document.getElementById('archive-security-pwd').value;
      if (!password) { toast('Please enter the security password.', true); return; }
      
      const btn = this;
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

      if (archiveSecurityCallback) {
          await archiveSecurityCallback(password);
      }

      btn.disabled = false;
      btn.innerHTML = originalText;
      document.getElementById('archiveSecurityModal').classList.remove('open');
  });

  async function updateArchivePassword(e) {
      e.preventDefault();
      const pwd = document.getElementById('sec-archive-pwd').value;
      const confirm = document.getElementById('sec-archive-pwd-confirm').value;
      
      if (pwd !== confirm) { toast('Passwords do not match.', true); return; }

      const btn = e.target.querySelector('button[type="submit"]');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';

      try {
          const res = await fetch('{{ route("super-admin.security.update-archive-password") }}', {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
              body: JSON.stringify({ archive_password: pwd, archive_password_confirmation: confirm })
          });
          const data = await res.json();
          if (data.success) {
              toast(data.message);
              e.target.reset();
          } else {
              toast(data.message || 'Error updating password.', true);
          }
      } catch (err) { toast('Network error.', true); }
      btn.disabled = false;
      btn.innerHTML = originalText;
  }

  async function deleteUserPermanently(id, name) {
      promptArchivePassword(async (password) => {
          try {
              const res = await fetch(`/super-admin/users/${id}`, {
                  method: 'DELETE',
                  headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                  body: JSON.stringify({ archive_password: password })
              });
              const data = await res.json();
              if (data.success) {
                  toast(data.message);
                  location.reload();
              } else {
                  toast(data.message || 'Error deleting user.', true);
              }
          } catch (err) { toast('Network error.', true); }
      });
  }

// ─── Create Staff ───────────────────────────────────────────────────────────────
function resetStaffForm() {
    document.getElementById('staffForm').reset();
    document.getElementById('staffForm').classList.remove('hidden');
    document.getElementById('staffSuccessMsg').classList.add('hidden');
}

async function submitStaff(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btn-save-staff');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    btn.disabled = true;

    try {
        const firstName = document.getElementById('staff-first').value.trim();
        const lastName = document.getElementById('staff-last').value.trim();
        const phone = document.getElementById('staff-phone').value.trim();
        const address = document.getElementById('staff-address').value.trim();
        const email = document.getElementById('staff-email').value.trim();
        const role = document.getElementById('staff-role').value;

        const nameRegex = /^[A-Za-z\s]+$/;
        if (!nameRegex.test(firstName) || firstName.length > 16) {
            toast('First Name must only contain letters and max 16 chars.', true);
            btn.innerHTML = originalText; btn.disabled = false; return;
        }
        if (!nameRegex.test(lastName) || lastName.length > 16) {
            toast('Last Name must only contain letters and max 16 chars.', true);
            btn.innerHTML = originalText; btn.disabled = false; return;
        }
        
        const phoneRegex = /^[0-9]+$/;
        if (phone && (!phoneRegex.test(phone) || phone.length > 11)) {
            toast('Phone Number must be up to 11 digits only.', true);
            btn.innerHTML = originalText; btn.disabled = false; return;
        }

        const addressRegex = /^[A-Za-z0-9\s.,'-]+$/;
        if (address && !addressRegex.test(address)) {
            toast('Home Address contains invalid special characters.', true);
            btn.innerHTML = originalText; btn.disabled = false; return;
        }

        const payload = {
            first_name: firstName,
            last_name: lastName,
            email: email,
            phone_number: phone,
            role: role,
            address: address,
        };

        const res = await fetch('{{ route('super-admin.store-staff') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        
        if (data.success) {
            toast(data.message);
            document.getElementById('staffForm').classList.add('hidden');
            document.getElementById('staffSuccessMsg').classList.remove('hidden');
            document.getElementById('generatedPassword').textContent = data.temp_password;
            
            // Reload user list in background if needed, but no auto-redirect
            // setTimeout(() => location.reload(), 3000); 
        } else {
            toast(data.message || 'Error occurred.', true);
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                toast(firstError, true);
            }
        }
    } catch (err) {
        toast('A network error occurred.', true);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Close modal on backdrop click
document.getElementById('pwModal').addEventListener('click', function(e) {
    if (e.target === this) closePwModal();
});
document.getElementById('roleModal').addEventListener('click', function(e) {
    if (e.target === this) closeRoleModal();
});
document.getElementById('roleDetailModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
document.getElementById('manageRolesModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});


// Init icons and logic on load or AJAX load
function initSuperAdmin() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    // Clear existing interval if any to prevent duplicates
    if (window.auditLogInterval) clearInterval(window.auditLogInterval);
    
    // Auto-refresh audit log every 30s if on audit tab
    window.auditLogInterval = setInterval(() => {
        const auditTab = document.getElementById('tab-audit');
        if (auditTab && !auditTab.classList.contains('hidden')) {
            if (typeof loadAuditLog === 'function') loadAuditLog();
        }
    }, 30000);
}

document.addEventListener('DOMContentLoaded', initSuperAdmin);
document.addEventListener('page:loaded', initSuperAdmin);

// Immediate execution in case script is loaded via AJAX after DOMContentLoaded
initSuperAdmin();
</script>
@endsection
