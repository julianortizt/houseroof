<div class="space-y-6">
    <div class="bg-white rounded-lg border shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">{{ __('mapscalculator.work_order') }} #{{ $workOrder->id }}</h3>
                <p class="text-gray-500 text-sm">{{ __('mapscalculator.project') }}: {{ $project->name }} • {{ __('mapscalculator.client') }}: {{ $project->client_name }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-xs font-medium text-gray-500">{{ __('mapscalculator.progress') }}</div>
                    <div class="text-2xl font-bold text-primary-600">{{ $this->getProgress() }}%</div>
                </div>
                <span class="px-3 py-1.5 text-sm font-medium rounded-full
                    @switch($workOrder->status)
                        @case('agendada') bg-blue-100 text-blue-800 @break
                        @case('en_preparacion') bg-yellow-100 text-yellow-800 @break
                        @case('en_ejecucion') bg-primary-100 text-primary-800 @break
                        @case('finalizada') bg-green-100 text-green-800 @break
                        @case('entregada') bg-gray-100 text-gray-800 @break
                        @case('cancelada') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch
                ">{{ $workOrder->status_label }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
            @if(auth()->user()->isMapscalculatorAdmin() || auth()->user()->isComercial())
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.assigned_to') }}</label>
                <select wire:model="assignedTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">{{ __('mapscalculator.unassigned') }}</option>
                    @foreach(\App\Models\User::query()->whereIn('mapscalculator_role', ['admin', 'comercial', 'tecnico'])->orderBy('name')->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <button wire:click="updateAssignment" class="mt-2 px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">{{ __('mapscalculator.save_assignment') }}</button>
            </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.scheduled_start') }}</label>
                <input type="date" wire:model="workOrder.scheduled_start" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $workOrder->scheduled_start ? $workOrder->scheduled_start->format('Y-m-d') : '' }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.scheduled_end') }}</label>
                <input type="date" wire:model="workOrder.scheduled_end" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $workOrder->scheduled_end ? $workOrder->scheduled_end->format('Y-m-d') : '' }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.actual_start') }}</label>
                <input type="date" wire:model="workOrder.actual_start" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $workOrder->actual_start ? $workOrder->actual_start->format('Y-m-d') : '' }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.actual_end') }}</label>
                <input type="date" wire:model="workOrder.actual_end" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $workOrder->actual_end ? $workOrder->actual_end->format('Y-m-d') : '' }}">
            </div>
        </div>

        <div class="flex gap-2">
            @foreach($this->availableStatuses() as $key => $label)
                <button wire:click="updateStatus('{{ $key }}')" class="px-3 py-1.5 text-sm {{ $workOrder->status === $key ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700' }} rounded hover:bg-primary-700 hover:text-white transition">{{ $label }}</button>
            @endforeach
            <button wire:click="updateDates" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">{{ __('mapscalculator.save_dates') }}</button>
        </div>

        <div class="mt-4 flex gap-2">
            <button wire:click="$set('showLogModal', true)" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('mapscalculator.add_to_log') }}
            </button>
        </div>
    </div>

    @if(auth()->user()->isMapscalculatorAdmin() || auth()->user()->isComercial())
        @php
            $materialCost = collect($inventoryMovements)->sum(fn ($movement) => (float) ($movement['total_cost'] ?? 0));
            $costingEnabled = app(\App\Services\PlatformModules::class)->enabled('inventory')
                && app(\App\Services\InventoryValuationService::class)->isCostingEnabled();
        @endphp
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ app()->getLocale() === 'es' ? 'Costes de la obra' : 'Work order costs' }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ app()->getLocale() === 'es' ? 'Los materiales solo se incluyen cuando el coste medio ponderado está activo.' : 'Materials are included only when weighted average costing is enabled.' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">{{ app()->getLocale() === 'es' ? 'Coste total registrado' : 'Recorded total cost' }}</p>
                    <p class="text-xl font-semibold text-gray-900">{{ number_format($materialCost + (float) $workOrder->labor_cost + (float) $workOrder->other_cost, 2, ',', '.') }} €</p>
                    @if(! $costingEnabled)
                        <p class="text-xs text-amber-700">{{ app()->getLocale() === 'es' ? 'Coste de materiales no valorado' : 'Material cost is not valued' }}</p>
                    @endif
                </div>
            </div>
            <form wire:submit.prevent="updateCosts" class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'es' ? 'Mano de obra (€)' : 'Labour (€)' }}</label>
                    <input wire:model="laborCost" type="number" min="0" max="9999999999.99" step="0.01" required class="w-full rounded-lg border-gray-300">
                    @error('laborCost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'es' ? 'Otros costes (€)' : 'Other costs (€)' }}</label>
                    <input wire:model="otherCost" type="number" min="0" max="9999999999.99" step="0.01" required class="w-full rounded-lg border-gray-300">
                    @error('otherCost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ app()->getLocale() === 'es' ? 'Notas de costes' : 'Cost notes' }}</label>
                    <textarea wire:model="costNotes" rows="2" maxlength="5000" class="w-full rounded-lg border-gray-300"></textarea>
                    @error('costNotes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        {{ app()->getLocale() === 'es' ? 'Guardar costes' : 'Save costs' }}
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section
        x-data="offlineWorkLogQueue({
            orderId: @js($workOrder->id),
            endpoint: @js(route('mapscalculator.work-order.sync-logs', $workOrder)),
            csrf: @js(csrf_token()),
            userId: @js(auth()->id()),
            spanish: @js(app()->getLocale() === 'es')
        })"
        x-init="init()"
        class="rounded-lg border bg-white p-6 shadow-sm"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-medium text-gray-900">{{ app()->getLocale() === 'es' ? 'Bitácora sin conexión' : 'Offline work log' }}</h3>
                <p class="mt-1 text-sm text-gray-500" x-text="online
                    ? (spanish ? 'Los apuntes guardados se sincronizan al recuperar la conexión.' : 'Saved notes sync when the connection returns.')
                    : (spanish ? 'Sin conexión: los apuntes se guardan en este dispositivo.' : 'Offline: notes are saved on this device.')"></p>
            </div>
            <button type="button" @click="sync()" :disabled="syncing || !online || queue.length === 0" class="rounded-lg border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50">
                <span x-text="syncing ? (spanish ? 'Sincronizando…' : 'Syncing…') : (spanish ? 'Sincronizar' : 'Sync')"></span>
                <span x-show="queue.length > 0" x-text="' (' + queue.length + ')'"></span>
            </button>
        </div>
        <form @submit.prevent="save()" class="grid gap-3 md:grid-cols-[12rem_1fr_auto]">
            <select x-model="type" class="rounded-lg border-gray-300">
                <option value="general">{{ __('mapscalculator.log_general') }}</option>
                <option value="antes">{{ __('mapscalculator.log_before') }}</option>
                <option value="durante">{{ __('mapscalculator.log_during') }}</option>
                <option value="despues">{{ __('mapscalculator.log_after') }}</option>
            </select>
            <textarea x-model="description" required maxlength="5000" rows="2" class="rounded-lg border-gray-300" :placeholder="spanish ? 'Describe el avance o una incidencia' : 'Describe progress or an issue'"></textarea>
            <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 font-medium text-white hover:bg-primary-700" x-text="spanish ? 'Guardar apunte' : 'Save note'"></button>
        </form>
        <p x-show="message" x-text="message" class="mt-3 text-sm text-green-700" role="status"></p>
        <p x-show="error" x-text="error" class="mt-3 text-sm text-red-700" role="alert"></p>
        <p class="mt-3 text-xs text-gray-500">{{ app()->getLocale() === 'es' ? 'Las fotos no se guardan sin conexión. No cierres sesión ni borres los datos del navegador antes de sincronizar.' : 'Photos are not saved offline. Do not sign out or clear browser data before syncing.' }}</p>
    </section>

    @if($this->inventoryEnabled())
        <section class="overflow-hidden rounded-lg border bg-white shadow-sm">
            <div class="flex items-center justify-between border-b p-4">
                <h3 class="font-medium text-gray-900">{{ __('mapscalculator.materials_consumption') }}</h3>
                <span class="text-sm text-gray-500">{{ count($inventoryMovements) }} {{ __('mapscalculator.records') }}</span>
            </div>

            @if(in_array($workOrder->status, ['en_preparacion', 'en_ejecucion'], true))
                <form wire:submit.prevent="consumeMaterial" class="grid gap-3 border-b bg-gray-50 p-4 md:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.inventory_item') }}</label>
                        <select wire:model="materialItemId" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">{{ __('mapscalculator.select_inventory_item') }}</option>
                            @foreach($this->availableInventoryItems() as $item)
                                <option value="{{ $item['id'] }}">{{ $item['sku'] }} — {{ $item['name'] }} ({{ $item['unit'] }})</option>
                            @endforeach
                        </select>
                        @error('inventory_item_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.inventory_location') }}</label>
                        <select wire:model="materialLocationId" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">{{ __('mapscalculator.select_inventory_location') }}</option>
                            @foreach($this->availableInventoryLocations() as $location)
                                <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                            @endforeach
                        </select>
                        @error('inventory_location_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.quantity') }}</label>
                        <input wire:model="materialQuantity" type="number" step="0.001" min="0.001" required class="w-full rounded-lg border-gray-300 text-sm">
                        @error('quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.notes') }}</label>
                        <input wire:model="materialNotes" type="text" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                            {{ __('mapscalculator.record_consumption') }}
                        </button>
                    </div>
                    @error('work_order')<p class="text-sm text-red-600 md:col-span-2 lg:col-span-5">{{ $message }}</p>@enderror
                </form>
            @else
                <p class="border-b bg-gray-50 p-4 text-sm text-gray-600">{{ __('mapscalculator.materials_require_active_work') }}</p>
            @endif

            <div class="border-b p-4">
                <h4 class="font-medium text-gray-900">{{ __('mapscalculator.material_reservations') }}</h4>
                @if(in_array($workOrder->status, ['en_preparacion', 'en_ejecucion'], true))
                    <form wire:submit.prevent="reserveMaterial" class="mt-3 grid gap-3 md:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.inventory_item') }}</label>
                            <select wire:model="reservationItemId" required class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">{{ __('mapscalculator.select_inventory_item') }}</option>
                                @foreach($this->availableInventoryItems() as $item)
                                    <option value="{{ $item['id'] }}">{{ $item['sku'] }} — {{ $item['name'] }} ({{ $item['unit'] }})</option>
                                @endforeach
                            </select>
                            @error('inventory_item_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.inventory_location') }}</label>
                            <select wire:model="reservationLocationId" required class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">{{ __('mapscalculator.select_inventory_location') }}</option>
                                @foreach($this->availableInventoryLocations() as $location)
                                    <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.reserved_quantity') }}</label>
                            <input wire:model="reservationQuantity" type="number" step="0.001" min="0.001" required class="w-full rounded-lg border-gray-300 text-sm">
                            @error('quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">{{ __('mapscalculator.notes') }}</label>
                            <input wire:model="reservationNotes" type="text" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full rounded-lg border border-primary-600 px-4 py-2 text-sm font-medium text-primary-700 hover:bg-primary-50">
                                {{ __('mapscalculator.reserve_material') }}
                            </button>
                        </div>
                    </form>
                @endif

                @if(empty($inventoryReservations))
                    <p class="mt-3 text-sm text-gray-500">{{ __('mapscalculator.no_material_reservations') }}</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2">{{ __('mapscalculator.inventory_item') }}</th>
                                    <th class="px-3 py-2">{{ __('mapscalculator.inventory_location') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('mapscalculator.reserved_quantity') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('mapscalculator.remaining_quantity') }}</th>
                                    <th class="px-3 py-2">{{ __('mapscalculator.status') }}</th>
                                    <th class="px-3 py-2">{{ __('mapscalculator.user') }}</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($inventoryReservations as $reservation)
                                    <tr>
                                        <td class="px-3 py-2">{{ $reservation['item']['sku'] ?? '' }} — {{ $reservation['item']['name'] ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $reservation['location']['name'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format((float) $reservation['quantity'], 3, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format((float) $reservation['quantity'] - (float) $reservation['consumed_quantity'], 3, ',', '.') }}</td>
                                        <td class="px-3 py-2">{{ __('mapscalculator.reservation_status_' . $reservation['status']) }}</td>
                                        <td class="px-3 py-2">{{ $reservation['creator']['name'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right">
                                            @if($reservation['status'] === 'active')
                                                <button wire:click="releaseMaterialReservation({{ $reservation['id'] }})" class="text-xs font-medium text-red-700 hover:underline">
                                                    {{ __('mapscalculator.release') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if(empty($inventoryMovements))
                <p class="p-6 text-center text-sm text-gray-500">{{ __('mapscalculator.no_materials_consumed') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('mapscalculator.created') }}</th>
                                <th class="px-4 py-3">{{ __('mapscalculator.inventory_item') }}</th>
                                <th class="px-4 py-3">{{ __('mapscalculator.inventory_location') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('mapscalculator.quantity') }}</th>
                                <th class="px-4 py-3">{{ __('mapscalculator.user') }}</th>
                                <th class="px-4 py-3">{{ __('mapscalculator.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($inventoryMovements as $movement)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3">{{ \Carbon\Carbon::parse($movement['created_at'])->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">{{ $movement['item']['sku'] ?? '' }} — {{ $movement['item']['name'] ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $movement['location']['name'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format(abs((float) $movement['quantity_change']), 3, ',', '.') }} {{ $movement['item']['unit'] ?? '' }}</td>
                                    <td class="px-4 py-3">{{ $movement['creator']['name'] ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $movement['notes'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <script>
        function offlineWorkLogQueue(config) {
            return {
                orderId: config.orderId,
                endpoint: config.endpoint,
                csrf: config.csrf,
                userId: config.userId,
                spanish: config.spanish,
                queue: [],
                description: '',
                type: 'general',
                online: navigator.onLine,
                syncing: false,
                message: '',
                error: '',
                get storageKey() {
                    return 'mapscalculator-offline-logs:' + this.userId + ':' + this.orderId;
                },
                init() {
                    try {
                        this.queue = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
                        if (!Array.isArray(this.queue)) throw new Error('Invalid offline queue');
                    } catch (error) {
                        this.error = this.spanish
                            ? 'No se pudo leer la cola local. No guardes más apuntes hasta revisar el almacenamiento del navegador.'
                            : 'Could not read the local queue. Do not add notes until browser storage is checked.';
                        return;
                    }
                    window.addEventListener('online', () => {
                        this.online = true;
                        this.sync();
                    });
                    window.addEventListener('offline', () => { this.online = false; });
                    if (this.online && this.queue.length) this.sync();
                },
                persist() {
                    localStorage.setItem(this.storageKey, JSON.stringify(this.queue));
                },
                save() {
                    const description = this.description.trim();
                    if (!description) return;
                    this.error = '';
                    this.message = '';
                    try {
                        this.queue.push({
                            client_uuid: crypto.randomUUID
                                ? crypto.randomUUID()
                                : ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, character =>
                                    (character ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> character / 4).toString(16)),
                            date: new Date().toISOString().slice(0, 10),
                            description,
                            type: this.type
                        });
                        this.persist();
                        this.description = '';
                        this.message = this.spanish ? 'Apunte guardado en la cola local.' : 'Note saved to the local queue.';
                        if (this.online) this.sync();
                    } catch (error) {
                        this.queue.pop();
                        this.error = this.spanish
                            ? 'No se pudo guardar el apunte en este dispositivo. Comprueba el espacio de almacenamiento.'
                            : 'Could not save this note on the device. Check available browser storage.';
                    }
                },
                async sync() {
                    if (!this.online || this.syncing || this.queue.length === 0) return;
                    this.syncing = true;
                    this.error = '';
                    try {
                        while (this.online && this.queue.length) {
                            const batch = this.queue.slice(0, 50);
                            const response = await fetch(this.endpoint, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf
                                },
                                body: JSON.stringify({ logs: batch })
                            });
                            const result = await response.json();
                            if (!response.ok) {
                                throw new Error(result.message || (this.spanish ? 'No se pudo sincronizar la bitácora.' : 'The work log could not be synced.'));
                            }
                            const accepted = new Set(result.synced || []);
                            this.queue = this.queue.filter(entry => !accepted.has(entry.client_uuid));
                            this.persist();
                            if (accepted.size !== batch.length) {
                                throw new Error(this.spanish ? 'El servidor no confirmó todos los apuntes.' : 'The server did not confirm every note.');
                            }
                        }
                        this.message = this.spanish ? 'Bitácora sincronizada.' : 'Work log synced.';
                        if (window.Livewire) {
                            window.Livewire.dispatch('logSaved');
                        }
                    } catch (error) {
                        this.error = error.message || (this.spanish ? 'Falló la sincronización. Los apuntes siguen guardados localmente.' : 'Sync failed. Notes remain saved locally.');
                    } finally {
                        this.syncing = false;
                    }
                }
            };
        }
    </script>

    <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">{{ __('mapscalculator.work_log') }}</h3>
            <span class="text-sm text-gray-500">{{ count($logs) }} {{ __('mapscalculator.records') }}</span>
        </div>

        @if(empty($logs))
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <p>{{ __('mapscalculator.no_logs') }}</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($logs as $log)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ $log['creator']['name'] ?? __('mapscalculator.user') }}</span>
                                    <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($log['date'])->format('d/m/Y H:i') }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                        @switch($log['type'])
                                            @case('antes') bg-blue-100 text-blue-800 @break
                                            @case('durante') bg-yellow-100 text-yellow-800 @break
                                            @case('despues') bg-green-100 text-green-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">{{ $log['type_label'] ?? ucfirst($log['type']) }}</span>
                                </div>
                                <p class="text-gray-700 mt-1 whitespace-pre-wrap">{{ $log['description'] }}</p>
                                @if($log['photo_path'])
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($log['photo_path']) }}" class="max-w-xs max-h-48 rounded-lg border" alt="{{ __('mapscalculator.work_photo') }}">
                                    </div>
                                @endif
                            </div>
                            <button wire:click="deleteLog({{ $log['id'] }})" class="text-red-500 hover:text-red-700 p-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v12m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12"></path></svg></button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- showLogModal es una propiedad de Livewire: sin el prefijo $wire Alpine
         no la encuentra y el modal nunca se abría. --}}
    <div x-show="$wire.showLogModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="$wire.set('showLogModal', false)">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
                <h3 class="text-lg font-semibold">{{ __('mapscalculator.add_log_entry') }}</h3>
                <button @click="$wire.set('showLogModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form wire:submit.prevent="addLog" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.log_type') }}</label>
                <select wire:model="newLogType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="antes">{{ __('mapscalculator.log_before') }}</option>
                    <option value="durante">{{ __('mapscalculator.log_during') }}</option>
                    <option value="despues">{{ __('mapscalculator.log_after') }}</option>
                    <option value="general">{{ __('mapscalculator.log_general') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.log_description') }} *</label>
                <textarea wire:model="newLogDescription" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="{{ __('mapscalculator.log_description_placeholder') }}"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.log_photo') }} ({{ __('mapscalculator.optional') }})</label>
                <input type="file" wire:model="newLogPhoto" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                @if($newLogPhoto)
                    <img src="{{ $newLogPhoto->temporaryUrl() }}" class="mt-2 max-h-40 rounded-lg" alt="{{ __('mapscalculator.photo_preview') }}">
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" @click="$wire.set('showLogModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.cancel') }}</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">{{ __('mapscalculator.save_log') }}</button>
            </div>
        </form>
    </div>
    </div>
</div>
