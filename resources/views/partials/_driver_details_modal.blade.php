    {{-- Driver Details Modal with Tabs --}}
    <div id="driverDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="driverDetailsName">Driver Details</h3>
                    <p class="text-sm text-gray-500" id="driverDetailsSubtitle"></p>
                </div>
                <button onclick="closeDriverDetails()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="px-6 pt-4 border-b">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button type="button" class="driver-tab active border-yellow-500 text-yellow-600 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="basic">
                        Basic Info
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="license">
                        License & Documents
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="incentives">
                        Incentives
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="performance">
                        Performance
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="insights">
                        Insights
                    </button>
                </nav>
            </div>

            {{-- Tab Panels --}}
            <div class="p-6 space-y-6">
                <div class="driver-tab-panel" data-tab-panel="basic">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Personal & Employment Details</h4>
                    <div id="basicInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <p class="text-gray-400">Loading...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="license">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">License Information</h4>
                    <div id="licenseInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <p class="text-gray-400">Loading...</p>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <h5 class="text-sm font-semibold text-gray-800 mb-2">Upload Driver Documents</h5>
                        <p class="text-xs text-gray-500 mb-3">Accepted file types: JPG, PNG, PDF. Uploads replace any existing document for the same type.</p>
                        <form id="driverDocumentsForm" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="driver_id" id="driverDocumentsDriverId" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver's License (Front)</label>
                                    <input type="file" name="license_front" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver's License (Back)</label>
                                    <input type="file" name="license_back" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">NBI Clearance</label>
                                    <input type="file" name="nbi_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Barangay Clearance</label>
                                    <input type="file" name="barangay_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Medical Certificate</label>
                                    <input type="file" name="medical_certificate" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                    Save Documents
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="incentives">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Rule-based Incentives</h4>
                    <p class="text-sm text-gray-600 mb-4">This section shows the breakdown of incentives based on rules like no damage, no absence, complete boundary payments, and no late return.</p>
                    <div id="incentivesContent" class="text-sm text-gray-700">
                        <p class="text-gray-400">Loading incentive data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="performance">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Performance Analytics</h4>
                    <div id="performanceContent" class="text-sm text-gray-700 space-y-2">
                        <p class="text-gray-400">Loading performance data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="insights">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">System Insights & Recommendations</h4>
                    <div id="insightsContent" class="text-sm text-gray-700 space-y-2">
                        <p class="text-gray-400">Loading insights...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

