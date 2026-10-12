<x-filament-panels::page>
    <div class="space-y-6">

        <!-- CARDS DE ESTATÍSTICAS (KPIs) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Geral -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Geral</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalGeral }}</span>
                    <span class="text-xs font-semibold px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg">🏋️‍♂️ Treinos</span>
                </div>
            </div>

            <!-- Total Mensal -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Este Mês</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-primary-600 dark:text-primary-400">{{ $totalMensal }}</span>
                    <span class="text-xs font-semibold px-2 py-1 bg-primary-50 text-primary-600 rounded-lg">📅 Dias</span>
                </div>
            </div>

            <!-- Total Semanal -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Esta Semana</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ $totalSemanal }}</span>
                    <span class="text-xs font-semibold px-2 py-1 bg-amber-50 text-amber-600 rounded-lg">⚡ Ativo</span>
                </div>
            </div>

            <!-- Sequência (Streak) -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sequência</span>
                <div class="flex items-baseline justify-between mt-2">
                    <span class="text-3xl font-black text-orange-600 dark:text-orange-400">{{ $sequenciaDias }} <span class="text-base font-bold">dias</span></span>
                    <span class="text-xs font-semibold px-2 py-1 bg-orange-50 text-orange-600 rounded-lg">🔥 Fogo</span>
                </div>
            </div>
        </div>

        <!-- BOTÃO DE ATALHO PARA O MODO TREINO -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 rounded-2xl shadow-lg text-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black">Pronto para treinar hoje? 💪</h3>
                <p class="text-emerald-100 text-sm mt-1">Aceda ao Modo Treino para registar os exercícios e cronometrar o descanso.</p>
            </div>
            <a href="{{ \App\Filament\Admin\Pages\ExecutarTreino::getUrl() }}" class="px-6 py-3 bg-white text-emerald-800 font-black rounded-xl shadow-md hover:bg-emerald-50 transition active:scale-95 whitespace-nowrap">
                🚀 Abrir Modo Treino
            </a>
        </div>

        <!-- CALENDÁRIO MENSAL ESTILIZADO -->
        @php
            $firstDayOfMonth = Carbon::create($currentYear, $currentMonth, 1);
            $daysInMonth = $firstDayOfMonth->daysInMonth;
            $startingDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 (Domingo) até 6 (Sábado)
            $nomeMes = ucfirst($firstDayOfMonth->locale('pt_PT')->monthName);
        @endphp

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden max-w-xl mx-auto">
            
            <!-- Cabeçalho do Calendário -->
            <div class="bg-primary-600 text-white p-4 flex items-center justify-between">
                <button wire:click="mudarMes(-1)" class="p-2 hover:bg-primary-700 rounded-xl transition font-bold">◀ Mês Anterior</button>
                <h2 class="text-lg font-black tracking-wider uppercase">{{ $nomeMes }} de {{ $currentYear }}</h2>
                <button wire:click="mudarMes(1)" class="p-2 hover:bg-primary-700 rounded-xl transition font-bold">Próximo Mês ▶</button>
            </div>

            <!-- Dias da Semana -->
            <div class="grid grid-cols-7 bg-gray-100 dark:bg-gray-800 text-center py-2 text-xs font-black text-gray-600 dark:text-gray-300 uppercase tracking-widest border-b border-gray-200 dark:border-gray-700">
                <div>D</div><div>S</div><div>T</div><div>Q</div><div>Q</div><div>S</div><div>S</div>
            </div>

            <!-- Grelha dos Dias -->
            <div class="grid grid-cols-7 gap-1 p-4 text-center">
                <!-- Espaços vazios antes do dia 1 -->
                @for ($i = 0; $i < $startingDayOfWeek; $i++)
                    <div></div>
                @endfor

                <!-- Dias do Mês -->
                @for ($dia = 1; $dia <= $daysInMonth; $dia++)
                    @php
                        $treinado = in_array($dia, $diasTreinadosNoMes);
                        $ehHoje = ($dia == Carbon::now()->day && $currentMonth == Carbon::now()->month && $currentYear == Carbon::now()->year);
                    @endphp

                    <div class="aspect-square flex flex-col items-center justify-center rounded-xl relative transition
                        {{ $treinado ? 'bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 text-emerald-900 dark:text-emerald-200 font-black' : 'bg-gray-50 dark:bg-gray-800/40 text-gray-700 dark:text-gray-300 font-semibold' }}
                        {{ $ehHoje && !$treinado ? 'border-2 border-primary-500' : '' }}">
                        
                        <span class="text-sm">{{ $dia }}</span>
                        
                        @if($treinado)
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">✅</span>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

    </div>
</x-filament-panels::page>