@extends('layouts.app')

@section('title', 'Coding Violations - Euro System')
@section('page-heading', 'Coding Violation History')
@section('page-subheading', 'History of units detected in restricted coding areas')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('coding.index') }}" class="flex items-center gap-2 text-sm text-gray-400 hover:text-yellow-600 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Coding Management
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center border border-red-100">
                <i data-lucide="alert-octagon" class="w-6 h-6 text-red-600"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Total Violations</div>
                <div class="text-2xl font-black text-gray-900 leading-none">{{ $pagination['total_items'] }}</div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center border border-blue-100">
                <i data-lucide="map-pin" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Impacted Units</div>
                <div class="text-2xl font-black text-gray-900 leading-none">{{ $violations->unique('unit_id')->count() }}</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center border border-yellow-100">
                <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Last Log</div>
                <div class="text-lg font-black text-gray-900 leading-none">{{ $violations->first() ? \Carbon\Carbon::parse($violations->first()->violation_time)->diffForHumans() : 'No Logs' }}</div>
            </div>
        </div>
    </div>

    <!-- Violations Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-6 border-b bg-gray-50/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h3 class="font-black text-gray-800 text-sm flex items-center gap-2 shrink-0">
                <i data-lucide="history" class="w-4 h-4 text-red-600"></i>
                Detection Logs
            </h3>
            
            <form method="GET" action="{{ route('coding.violations') }}" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-44">
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                        class="block w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-xs font-bold text-gray-700">
                </div>
                <div class="relative w-full md:w-48">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-3 w-3 text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search plate..."
                        class="block w-full pl-8 pr-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-xs font-bold text-gray-700">
                </div>
                @if($date || $search)
                    <a href="{{ route('coding.violations') }}" class="p-2 text-gray-400 hover:text-red-500 transition-colors flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </a>
                @endif
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Time Detected</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit / Plate</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Violation Type</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Restricted Location</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Map Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($violations as $v)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-900 leading-none mb-1">{{ \Carbon\Carbon::parse($v->violation_time)->format('M d, Y') }}</div>
                                <div class="text-[10px] text-gray-400 font-black tracking-widest">{{ \Carbon\Carbon::parse($v->violation_time)->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center font-black text-xs text-gray-500 uppercase">
                                        {{ substr($v->make ?? 'T', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-gray-900 leading-none mb-1">{{ $v->plate_number }}</div>
                                        <div class="text-[10px] text-gray-400 font-bold leading-none">{{ $v->make }} {{ $v->model }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-[9px] font-black uppercase tracking-widest rounded-full {{ strpos($v->violation_type, 'Makati') !== false ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                    {{ $v->violation_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-red-400"></i>
                                    <span class="text-xs font-bold text-gray-600">{{ $v->location_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="https://www.google.com/maps?q={{ $v->latitude }},{{ $v->longitude }}" target="_blank" class="p-2 bg-gray-50 rounded-lg text-gray-400 hover:text-blue-600 transition-colors inline-block border border-transparent hover:border-blue-100">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <i data-lucide="shield-check" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                                <div class="text-lg font-black text-gray-300">No Violations Found</div>
                                <div class="text-xs text-gray-400">All units are following the coding rules.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pagination['total_pages'] > 1)
            <div class="px-6 py-4 border-t bg-gray-50/50 flex justify-between items-center">
                <div class="text-xs font-bold text-gray-500">
                    Showing {{ $violations->count() }} of {{ $pagination['total_items'] }} violations
                </div>
                <div class="flex gap-2">
                    @if($pagination['has_prev'])
                        <a href="?page={{ $pagination['prev_page'] }}" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50">Previous</a>
                    @endif
                    @if($pagination['has_next'])
                        <a href="?page={{ $pagination['next_page'] }}" class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50">Next</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
