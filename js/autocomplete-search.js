/**
 * Real-time Search Autocomplete Functionality
 * Provides instant suggestions as user types
 */

class AutocompleteSearch {
    constructor(options = {}) {
        this.options = {
            minChars: 2,
            maxResults: 10,
            debounceDelay: 300,
            ...options
        };
        
        this.cache = new Map();
        this.debounceTimer = null;
        this.currentRequest = null;
        this.activeIndex = -1;
    }

    /**
     * Initialize autocomplete on search input
     */
    init(inputSelector, suggestionType = 'global', onSelectCallback = null) {
        const input = document.querySelector(inputSelector);
        if (!input) return;

        this.input = input;
        this.suggestionType = suggestionType;
        this.onSelectCallback = onSelectCallback;

        // Create dropdown container
        this.createDropdown();

        // Event listeners
        input.addEventListener('input', (e) => this.handleInput(e));
        input.addEventListener('focus', (e) => this.handleInput(e));
        input.addEventListener('blur', () => this.hideDropdown());
        input.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.autocomplete-container')) {
                this.hideDropdown();
            }
        });
    }

    /**
     * Create dropdown element
     */
    createDropdown() {
        const container = document.createElement('div');
        container.className = 'autocomplete-container relative';
        
        const dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden';
        dropdown.innerHTML = `
            <div class="autocomplete-loading hidden p-3 text-center text-gray-500 text-sm">
                <i class="fas fa-spinner fa-spin mr-2"></i>Searching...
            </div>
            <div class="autocomplete-results"></div>
            <div class="autocomplete-no-results hidden p-3 text-center text-gray-500 text-sm">
                No results found
            </div>
        `;

        // Wrap input with container
        this.input.parentNode.insertBefore(container, this.input);
        container.appendChild(this.input);
        container.appendChild(dropdown);

        this.dropdown = dropdown;
        this.resultsContainer = dropdown.querySelector('.autocomplete-results');
        this.loadingElement = dropdown.querySelector('.autocomplete-loading');
        this.noResultsElement = dropdown.querySelector('.autocomplete-no-results');
    }

    /**
     * Handle input events
     */
    handleInput(e) {
        const query = e.target.value.trim();
        
        if (query.length < this.options.minChars) {
            this.hideDropdown();
            return;
        }

        // Debounce search
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.search(query);
        }, this.options.debounceDelay);
    }

    /**
     * Handle keyboard navigation
     */
    handleKeydown(e) {
        const items = this.resultsContainer.querySelectorAll('.autocomplete-item');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.activeIndex = Math.min(this.activeIndex + 1, items.length - 1);
                this.updateActiveItem(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.activeIndex = Math.max(this.activeIndex - 1, -1);
                this.updateActiveItem(items);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.activeIndex >= 0 && items[this.activeIndex]) {
                    items[this.activeIndex].click();
                } else {
                    this.hideDropdown();
                }
                break;
                
            case 'Escape':
                this.hideDropdown();
                break;
        }
    }

    /**
     * Update active item styling
     */
    updateActiveItem(items) {
        items.forEach((item, index) => {
            if (index === this.activeIndex) {
                item.classList.add('bg-yellow-50', 'border-yellow-500');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-yellow-50', 'border-yellow-500');
            }
        });
    }

    /**
     * Search for suggestions
     */
    async search(query) {
        // Check cache first
        const cacheKey = `${this.suggestionType}:${query}`;
        if (this.cache.has(cacheKey)) {
            this.showResults(this.cache.get(cacheKey));
            return;
        }

        // Cancel previous request
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        this.showLoading();

        try {
            const endpoint = this.getEndpoint();
            const url = `${endpoint}?search=${encodeURIComponent(query)}`;
            
            this.currentRequest = fetch(url);
            const response = await this.currentRequest;
            const data = await response.json();

            this.cache.set(cacheKey, data);
            this.showResults(data);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                this.showError();
            }
        } finally {
            this.hideLoading();
            this.currentRequest = null;
        }
    }

    /**
     * Get API endpoint based on suggestion type
     */
    getEndpoint() {
        const endpoints = {
            'units': '/search/suggestions/units',
            'drivers': '/search/suggestions/drivers',
            'expenses': '/search/suggestions/expenses',
            'maintenance': '/search/suggestions/maintenance',
            'global': '/search/suggestions/global'
        };
        
        return endpoints[this.suggestionType] || endpoints['global'];
    }

    /**
     * Show loading state
     */
    showLoading() {
        this.dropdown.classList.remove('hidden');
        this.loadingElement.classList.remove('hidden');
        this.resultsContainer.innerHTML = '';
        this.noResultsElement.classList.add('hidden');
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        this.loadingElement.classList.add('hidden');
    }

    /**
     * Show search results
     */
    showResults(results) {
        this.dropdown.classList.remove('hidden');
        this.resultsContainer.innerHTML = '';
        this.noResultsElement.classList.add('hidden');
        this.activeIndex = -1;

        if (results.length === 0) {
            this.showNoResults();
            return;
        }

        results.forEach((result, index) => {
            const item = this.createResultItem(result, index);
            this.resultsContainer.appendChild(item);
        });
    }

    /**
     * Create result item element
     */
    createResultItem(result, index) {
        const item = document.createElement('div');
        item.className = 'autocomplete-item px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors';
        item.setAttribute('data-index', index);
        
        let content = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-lg">${result.icon || '📄'}</span>
                    <div>
                        <div class="font-medium text-gray-900">${this.escapeHtml(result.text)}</div>
        `;

        // Add additional info based on type
        if (result.type === 'unit') {
            content += `<div class="text-xs text-gray-500">Status: ${result.status || 'Unknown'}</div>`;
        } else if (result.type === 'driver') {
            content += `<div class="text-xs text-gray-500">Status: ${result.status || 'Unknown'}</div>`;
        } else if (result.type === 'expense') {
            content += `<div class="text-xs text-gray-500">${result.category || 'Uncategorized'} • ${result.amount || '₱0'}</div>`;
        } else if (result.type === 'maintenance') {
            content += `<div class="text-xs text-gray-500">Status: ${result.status || 'Unknown'}</div>`;
        }

        content += `
                    </div>
                </div>
                <div class="text-xs text-gray-400">
                    ${result.type || 'item'}
                </div>
            </div>
        `;

        item.innerHTML = content;

        // Click handler
        item.addEventListener('click', () => {
            this.selectResult(result);
        });

        return item;
    }

    /**
     * Show no results message
     */
    showNoResults() {
        this.dropdown.classList.remove('hidden');
        this.resultsContainer.innerHTML = '';
        this.noResultsElement.classList.remove('hidden');
    }

    /**
     * Show error message
     */
    showError() {
        this.resultsContainer.innerHTML = `
            <div class="p-3 text-center text-red-500 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Error loading results. Please try again.
            </div>
        `;
        this.dropdown.classList.remove('hidden');
    }

    /**
     * Select a result
     */
    selectResult(result) {
        this.input.value = result.text;
        this.hideDropdown();

        // Call callback if provided
        if (this.onSelectCallback) {
            this.onSelectCallback(result);
        }

        // Trigger change event
        this.input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /**
     * Hide dropdown
     */
    hideDropdown() {
        this.dropdown.classList.add('hidden');
        this.activeIndex = -1;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize autocomplete searches when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Units page search
    const unitsSearch = document.querySelector('#tableSearchInput');
    if (unitsSearch) {
        const autocomplete = new AutocompleteSearch({
            minChars: 1,
            debounceDelay: 200
        });
        autocomplete.init('#tableSearchInput', 'units', function(result) {
            // Auto-submit form when selection is made
            unitsSearch.closest('form').submit();
        });
    }

    // Global search (if exists)
    const globalSearch = document.querySelector('#globalSearchInput');
    if (globalSearch) {
        const autocomplete = new AutocompleteSearch({
            minChars: 2,
            debounceDelay: 300
        });
        autocomplete.init('#globalSearchInput', 'global', function(result) {
            // Handle global search selection
            console.log('Selected:', result);
        });
    }

    // Expense search
    const expenseSearch = document.querySelector('#expenseSearchInput');
    if (expenseSearch) {
        const autocomplete = new AutocompleteSearch({
            minChars: 1,
            debounceDelay: 200
        });
        autocomplete.init('#expenseSearchInput', 'expenses', function(result) {
            expenseSearch.closest('form').submit();
        });
    }

    // Maintenance search
    const maintenanceSearch = document.querySelector('#maintenanceSearchInput');
    if (maintenanceSearch) {
        const autocomplete = new AutocompleteSearch({
            minChars: 1,
            debounceDelay: 200
        });
        autocomplete.init('#maintenanceSearchInput', 'maintenance', function(result) {
            maintenanceSearch.closest('form').submit();
        });
    }

    // Driver search
    const driverSearch = document.querySelector('#driverSearchInput');
    if (driverSearch) {
        const autocomplete = new AutocompleteSearch({
            minChars: 1,
            debounceDelay: 200
        });
        autocomplete.init('#driverSearchInput', 'drivers', function(result) {
            driverSearch.closest('form').submit();
        });
    }
});

// Export for use in other scripts
window.AutocompleteSearch = AutocompleteSearch;
