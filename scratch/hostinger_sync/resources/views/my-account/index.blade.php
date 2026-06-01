@extends('layouts.app')

@section('page-heading', 'My Account')
@section('page-subheading', 'Manage your profile and account settings')

@section('content')
<div class="max-w-7xl mx-auto px-3 py-4">
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Left Column: Profile Info + Account Stats -->
        <div class="lg:col-span-2 space-y-3">
            
            <!-- Profile Header with Account Stats -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <!-- Profile Info -->
                    <div class="flex items-center gap-5">
                        <div class="flex flex-col items-center gap-1">
                            <div class="relative group cursor-pointer" onclick="openProfileModal()">
                                @if($user->profile_image)
                                    @php
                                        $imagePath = str_replace('resources/', '', $user->profile_image);
                                        $isIcon = str_contains($imagePath, 'image/') && !str_contains($imagePath, 'storage/');
                                    @endphp
                                    @if($isIcon)
                                        <img src="{{ asset($imagePath) }}" alt="Profile" class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-yellow-50 group-hover:opacity-75 transition-opacity">
                                    @else
                                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile" class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-yellow-50 group-hover:opacity-75 transition-opacity">
                                    @endif
                                @else
                                    <div class="w-20 h-20 bg-yellow-600 rounded-full flex items-center justify-center text-white text-2xl font-semibold flex-shrink-0 shadow-sm border-2 border-yellow-50 group-hover:bg-yellow-700 transition-colors">
                                        {{ strtoupper(substr($user->full_name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i data-lucide="camera" class="w-6 h-6 text-white drop-shadow-md"></i>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider cursor-pointer hover:text-yellow-600 transition-colors leading-none" onclick="openProfileModal()">Change Profile</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $user->full_name ?? 'User' }}</h1>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="text-sm font-semibold text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full">{{ $user->role === 'super_admin' ? 'Owner' : ucfirst($user->role) }}</span>
                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    Joined {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Statistics -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100/50 shadow-sm">
                            <div class="flex items-center justify-center gap-1 text-yellow-600 mb-2">
                                <i data-lucide="history" class="w-5 h-5"></i>
                            </div>
                            <p class="text-[11px] uppercase font-bold text-gray-400 tracking-widest">Last Login</p>
                            <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $user->last_login ? $user->last_login->format('M d, Y') : 'First time' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100/50 shadow-sm">
                            <div class="flex items-center justify-center gap-1 text-green-600 mb-2">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </div>
                            <p class="text-[11px] uppercase font-bold text-gray-400 tracking-widest">Status</p>
                            <p class="text-sm font-bold text-green-600 mt-0.5">{{ $user->is_active ? 'Active' : 'Inactive' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100/50 shadow-sm">
                            <div class="flex items-center justify-center gap-1 text-blue-600 mb-2">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <p class="text-[11px] uppercase font-bold text-gray-400 tracking-widest">Role</p>
                            <p class="text-sm font-bold text-blue-600 mt-0.5">{{ $user->role === 'super_admin' ? 'Owner' : ucfirst($user->role) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Form -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-2 border-b">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        Profile Information
                    </h2>
                </div>
                <form method="POST" action="{{ route('my-account.update-profile') }}" class="p-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" value="{{ $user->email }}" readonly
                                   class="w-full px-3 py-2 text-sm border border-gray-100 bg-gray-50 rounded-xl text-gray-500 cursor-not-allowed font-medium">
                            <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3"></i> Use the "Change Email" section below to update this.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">First Name</label>
                            <input type="text" name="first_name" value="{{ $user->first_name }}" required
                                   maxlength="18" oninput="formatName(this)"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ $user->middle_name }}"
                                   maxlength="18" oninput="formatName(this)"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Last Name</label>
                            <input type="text" name="last_name" value="{{ $user->last_name }}" required
                                   maxlength="18" oninput="formatName(this)"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number (11 Digits)</label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ $user->phone_number ?? '' }}" required
                                   maxlength="11"
                                   oninput="validatePhone(this)"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-yellow-500 focus:border-transparent font-bold tracking-widest transition-all"
                                   placeholder="09XXXXXXXXX">
                            <p class="text-[10px] text-gray-400 mt-1">Must start with 09 (e.g. 09123456789)</p>
                        </div>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="px-3 py-1 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700 flex items-center gap-1">
                            <i data-lucide="save" class="w-3 h-3"></i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Password Settings -->
        <div class="space-y-3">
            
            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-2 border-b">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Change Password
                    </h2>
                </div>
                <form method="POST" action="{{ route('my-account.change-password') }}" class="p-4 space-y-4">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Current Password</label>
                            <div class="relative group">
                                <input type="password" name="current_password" id="current_password" required
                                       class="w-full pl-3 pr-10 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                                <button type="button" onclick="togglePassword('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4" id="eye-current_password"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">New Password</label>
                            <div class="relative group">
                                <input type="password" name="password" id="new_password" required
                                       class="w-full pl-3 pr-10 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                                <button type="button" onclick="togglePassword('new_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4" id="eye-new_password"></i>
                                </button>
                            </div>
                            {{-- Password Criteria --}}
                            <div class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                    <i data-lucide="shield-check" class="w-3 h-3 text-yellow-500"></i> Required Criteria:
                                </p>
                                <ul class="space-y-1.5" id="password-requirements">
                                    <li id="req-length" class="flex items-center gap-2 text-[10px] font-bold text-gray-400 transition-colors">
                                        <i data-lucide="circle" class="w-2.5 h-2.5 req-icon"></i> Minimum 8 characters
                                    </li>
                                    <li id="req-alphanumeric" class="flex items-center gap-2 text-[10px] font-bold text-gray-400 transition-colors">
                                        <i data-lucide="circle" class="w-2.5 h-2.5 req-icon"></i> Mix of letters and numbers
                                    </li>
                                    <li id="req-special" class="flex items-center gap-2 text-[10px] font-bold text-gray-400 transition-colors">
                                        <i data-lucide="circle" class="w-2.5 h-2.5 req-icon"></i> At least one special character
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Confirm New Password</label>
                            <div class="relative group">
                                <input type="password" name="password_confirmation" id="confirm_password" required
                                       class="w-full pl-3 pr-10 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                                <button type="button" onclick="togglePassword('confirm_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4" id="eye-confirm_password"></i>
                                </button>
                            </div>
                            <p id="match-error" class="hidden text-[10px] font-bold text-red-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i> Passwords do not match
                            </p>
                            <p id="match-success" class="hidden text-[10px] font-bold text-green-500 mt-1.5 flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> Passwords match
                            </p>
                        </div>
                    </div>
                    <button type="submit" id="submit-btn" disabled
                            class="w-full mt-2 px-4 py-2.5 bg-gray-200 text-gray-400 text-xs font-black uppercase tracking-widest rounded-xl cursor-not-allowed transition-all flex items-center justify-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Update Account Password
                    </button>
                </form>
            </div>

            <!-- Change Email -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-2 border-b">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1">
                        <i data-lucide="mail" class="w-4 h-4 text-blue-600"></i>
                        Change Email Address
                    </h2>
                </div>
                <form method="POST" action="{{ route('my-account.request-email-change') }}" class="p-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">New Email Address</label>
                        <input type="email" name="new_email" required placeholder="newemail@gmail.com"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Verify with Password</label>
                        <input type="password" name="current_password" required placeholder="Enter current password"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                            <i data-lucide="shield-alert" class="w-3 h-3"></i> Security Step:
                        </p>
                        <p class="text-[10px] font-bold text-blue-500 leading-relaxed">
                            A confirmation link will be sent to your **current email ({{ $user->email }})**. You must click that link to authorize the change.
                        </p>
                    </div>

                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Request Email Change
                    </button>
                </form>
            </div>

            <!-- Test Push Notifications Card -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-2 border-b">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1">
                        <i data-lucide="smartphone" class="w-4 h-4 text-purple-600"></i>
                        Test App Push Notifications
                    </h2>
                </div>
                <div class="p-4 space-y-3">
                    <p class="text-[10px] font-bold text-gray-500 leading-relaxed">
                        Test if your Android device is registered correctly and can receive Real-time Firebase Push Notifications even when the app is completely closed.
                    </p>
                    
                    <div id="fcm-status-container">
                    @if(auth()->user()->fcm_token)
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100 flex items-start gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-purple-600 shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Device Registered</p>
                                <p class="text-[9px] text-purple-600 font-semibold truncate max-w-[200px]">Token: {{ substr(auth()->user()->fcm_token, 0, 20) }}...</p>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 flex items-start gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">No Device Registered</p>
                                <p class="text-[9px] text-amber-600 font-semibold leading-relaxed">Please log in to the Euro Taxi app on your Android phone first to register your device.</p>
                            </div>
                        </div>
                    @endif
                    </div>

                     <button type="button" id="btnTestPush" onclick="sendTestPush()" 
                            {{ auth()->user()->fcm_token ? '' : 'disabled' }}
                            class="w-full px-4 py-2.5 bg-purple-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-purple-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed shadow-lg shadow-purple-100 disabled:shadow-none transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Send Test Push
                    </button>

                    <!-- Real-Time Web-Bridge Chime Test -->
                    <button type="button" id="btnTestWebChime" onclick="triggerTestNotificationBroadcast()" 
                            class="w-full px-4 py-2.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-600 hover:to-amber-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-yellow-100 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-2 animate-bounce"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                        Send Web-Chime Test (Bypass)
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Profile Image Modal -->
<div id="profileModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeProfileModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2" id="modal-title">
                            <i data-lucide="user-circle" class="w-5 h-5 text-yellow-600"></i>
                            Update Profile Image
                        </h3>
                        <div class="mt-4 space-y-6">
                            <!-- Upload Section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload custom image</label>
                                <form id="uploadForm" action="{{ route('my-account.update-profile-image') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="profile_image" accept="image/*" onchange="submitUpload()"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 cursor-pointer">
                                </form>
                            </div>

                            <div class="relative py-2">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="w-full border-t border-gray-200"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 bg-white text-gray-500 uppercase tracking-wider text-[10px] font-bold">Or choose an icon</span>
                                </div>
                            </div>

                            <!-- Icon Grid -->
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-4">
                                @php
                                    $icons = [
                                        ['name' => 'Manager', 'file' => 'Manager.png'],
                                        ['name' => 'Mechanic', 'file' => 'Mechanic.png'],
                                        ['name' => 'Secretary', 'file' => 'secretary.png'],
                                        ['name' => 'Secretary 2', 'file' => 'secretary2.png'],
                                        ['name' => 'Manager 2', 'file' => 'manager2.png'],
                                    ];
                                @endphp
                                @foreach($icons as $icon)
                                    <div class="group cursor-pointer text-center" onclick="selectIcon('image/{{ $icon['file'] }}')">
                                        <div class="aspect-square rounded-lg border-2 border-gray-100 p-2 group-hover:border-yellow-500 group-hover:bg-yellow-50 transition-all mb-1">
                                            <img src="{{ asset('image/' . $icon['file']) }}" alt="{{ $icon['name'] }}" class="w-full h-full object-contain">
                                        </div>
                                        <span class="text-[10px] font-medium text-gray-500 group-hover:text-yellow-700">{{ $icon['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeProfileModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
                <form id="iconForm" action="{{ route('my-account.update-profile-image') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="icon_path" id="iconPathInput">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openProfileModal() {
        document.getElementById('profileModal').classList.remove('hidden');
    }

    function closeProfileModal() {
        document.getElementById('profileModal').classList.add('hidden');
    }

    function submitUpload() {
        document.getElementById('uploadForm').submit();
    }

    function selectIcon(path) {
        document.getElementById('iconPathInput').value = path;
        document.getElementById('iconForm').submit();
    }

    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = document.getElementById('eye-' + id);
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) lucide.createIcons();
    }

    function validatePhone(input) {
        // Remove non-digits
        let val = input.value.replace(/\D/g, '');
        
        // If empty, keep empty
        if (val === "") {
            input.value = "";
            return;
        }

        // Handle prepending 09
        if (val.length === 1) {
            // If they typed 9, make it 09
            if (val === "9") {
                val = "09";
            } else if (val !== "0") {
                // If they typed any other number (e.g. 8), make it 098
                val = "09" + val;
            }
        } else if (val.length >= 2) {
            // Ensure starts with 09
            if (!val.startsWith('09')) {
                // If it starts with 9 (but not 09), prepend 0
                if (val.startsWith('9')) {
                    val = '0' + val;
                } else {
                    // Otherwise force 09 prefix
                    val = '09' + val;
                }
            }
        }
        
        // Max 11 digits
        if (val.length > 11) {
            val = val.slice(0, 11);
        }
        
        input.value = val;
    }

    function formatName(input) {
        // 1. Remove numbers and symbols (allow only letters and spaces)
        let val = input.value.replace(/[^a-zA-Z\s]/g, '');
        
        // 2. Prohibit multiple spaces (allow only single space)
        val = val.replace(/\s\s+/g, ' ');
        
        // 3. Prohibit leading space
        if (val === ' ') {
            val = '';
        }

        // 4. Auto-correct: Title Case (First letter Caps, others small)
        // If it's a multi-word name, capitalize each word
        let words = val.split(' ');
        for (let i = 0; i < words.length; i++) {
            if (words[i].length > 0) {
                words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1).toLowerCase();
            }
        }
        val = words.join(' ');

        // 5. Max 18 characters
        if (val.length > 18) {
            val = val.slice(0, 18);
        }

        input.value = val;
    }

    // Password Validation Logic
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submit-btn');

    const reqs = {
        length: { el: document.getElementById('req-length'), regex: /.{8,}/ },
        alphanumeric: { el: document.getElementById('req-alphanumeric'), regex: /^(?=.*[a-zA-Z])(?=.*[0-9])/ },
        special: { el: document.getElementById('req-special'), regex: /[!@#$%^&*(),.?":{}|<>]/ }
    };

    function validatePassword() {
        // Strip spaces in real-time
        newPass.value = newPass.value.replace(/\s/g, '');
        confirmPass.value = confirmPass.value.replace(/\s/g, '');

        const val = newPass.value;
        const confirmVal = confirmPass.value;
        let allValid = true;

        // Check each requirement
        for (const key in reqs) {
            const isValid = reqs[key].regex.test(val);
            const el = reqs[key].el;
            const icon = el.querySelector('.req-icon');
            
            if (isValid && val.length > 0) {
                el.classList.remove('text-gray-400');
                el.classList.add('text-green-500');
                icon.setAttribute('data-lucide', 'check-circle-2');
            } else {
                el.classList.remove('text-green-500');
                el.classList.add('text-gray-400');
                icon.setAttribute('data-lucide', 'circle');
                allValid = false;
            }
        }

        // Check Match
        const matchError = document.getElementById('match-error');
        const matchSuccess = document.getElementById('match-success');
        
        if (confirmVal.length > 0) {
            if (val === confirmVal) {
                matchError.classList.add('hidden');
                matchSuccess.classList.remove('hidden');
            } else {
                matchError.classList.remove('hidden');
                matchSuccess.classList.add('hidden');
                allValid = false;
            }
        } else {
            matchError.classList.add('hidden');
            matchSuccess.classList.add('hidden');
            allValid = false;
        }

        // Update Submit Button
        if (allValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
            submitBtn.classList.add('bg-yellow-600', 'text-white', 'hover:bg-yellow-700', 'shadow-lg', 'shadow-yellow-100');
            submitBtn.innerHTML = '<i data-lucide="key" class="w-4 h-4"></i> Update Account Password';
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
            submitBtn.classList.remove('bg-yellow-600', 'text-white', 'hover:bg-yellow-700', 'shadow-lg', 'shadow-yellow-100');
            submitBtn.innerHTML = '<i data-lucide="lock" class="w-4 h-4"></i> Update Account Password';
        }

        if (window.lucide) lucide.createIcons();
    }

    newPass.addEventListener('input', validatePassword);
    confirmPass.addEventListener('input', validatePassword);

    // Send Test Push Notification via web route
    async function sendTestPush() {
        const btn = document.getElementById('btnTestPush');
        if (!btn) return;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Sending...';
        if (window.lucide) lucide.createIcons();

        try {
            const response = await fetch('{{ route("my-account.test-push") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    title: '🚨 Euro Taxi Test Notification',
                    body: 'Congratulations! Your Android device has been registered successfully and can receive real-time push notifications!',
                    type: 'system_alert'
                })
            });
            const data = await response.json();
            if (data.success) {
                alert('Success! Test push notification has been sent via Firebase.');
            } else {
                alert('Failed to send test push: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred while sending test push notification.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (window.lucide) lucide.createIcons();
        }
    }

    // Dynamic Real-time FCM Device Registration UI Handler
    window.addEventListener('fcm_token_synced_event', function(e) {
        const container = document.getElementById('fcm-status-container');
        const btn = document.getElementById('btnTestPush');
        if (container) {
            const tokenAbbrev = e.detail.token.substring(0, 20) + '...';
            container.innerHTML = `
                <div class="p-3 bg-purple-50 rounded-xl border border-purple-100 flex items-start gap-2 animate-bounce">
                    <i data-lucide="check-circle" class="w-4 h-4 text-purple-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Device Registered (Just Now!)</p>
                        <p class="text-[9px] text-purple-600 font-semibold truncate max-w-[200px]">Token: ${tokenAbbrev}</p>
                    </div>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
        }
        if (btn) {
            btn.removeAttribute('disabled');
            btn.disabled = false;
        }
    });

    // Close on escape key
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeProfileModal();
        }
    });
</script>
@endsection
