<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Balance de comprobación interno</x-slot>
            <p class="text-sm text-warning-700 dark:text-warning-300">
                Informe interno del diario genérico. No constituye un libro contable oficial ni incluye cierres, impuestos o conciliación bancaria.
            </p>
        </x-filament::section>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Cuenta</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3 text-right">Debe</th>
                        <th class="px-4 py-3 text-right">Haber</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $row['code'] }}</td>
                            <td class="px-4 py-3">{{ $row['name'] }}</td>
                            <td class="px-4 py-3">{{ $row['type'] }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float) $row['debit'], 2, ',', '.') }} €</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float) $row['credit'], 2, ',', '.') }} €</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format((float) $row['balance'], 2, ',', '.') }} €</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Aún no hay apuntes contabilizados.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t bg-gray-50 font-semibold dark:bg-gray-800">
                    <tr>
                        <td colspan="3" class="px-4 py-3">Totales Debe / Haber</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $totalDebit, 2, ',', '.') }} €</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $totalCredit, 2, ',', '.') }} €</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>
