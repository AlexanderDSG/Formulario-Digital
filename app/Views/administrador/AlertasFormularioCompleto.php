<!-- Loading indicator -->
<div id="loadingIndicator" class="hidden bg-blue-100 text-blue-700 p-4 rounded mb-4 border-l-4 border-blue-500">
    <div class="flex items-center">
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        ⏳ Cargando datos del formulario...
    </div>
</div>

<!-- Error message -->
<div id="errorMessage" class="hidden bg-red-100 text-red-700 p-4 rounded mb-4 border-l-4 border-red-500">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="errorText"></span>
    </div>
</div>

<!-- Info message -->
<div id="infoMessage" class="hidden bg-yellow-100 text-yellow-700 p-4 rounded mb-4 border-l-4 border-yellow-500">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="infoText"></span>
    </div>
</div>

<!-- Success message -->
<div id="successMessage" class="hidden bg-green-100 text-green-700 p-4 rounded mb-4 border-l-4 border-green-500">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="successText"></span>
    </div>
</div>