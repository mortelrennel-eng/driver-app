
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

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
let currentAccessUserId = null;

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
let auditTimer;
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
  let archiveSecurityCallback = null;

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


// Init icons on load
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    // Auto-refresh audit log every 30s if on audit tab
    setInterval(() => {
        if (!document.getElementById('tab-audit').classList.contains('hidden')) {
            loadAuditLog();
        }
    }, 30000);
});

