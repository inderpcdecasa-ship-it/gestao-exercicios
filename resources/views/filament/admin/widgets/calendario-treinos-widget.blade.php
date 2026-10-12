<x-filament-widgets::widget>
    <div style="display: flex; flex-direction: column; gap: 24px; width: 100%;">
        <!-- 1. CALENDÁRIO MENSAL (RETANGULAR E COMPACTO) -->
        @php
            $firstDayOfMonth = \Illuminate\Support\Carbon::create($this->currentYear, $this->currentMonth, 1);
            $daysInMonth = $firstDayOfMonth->daysInMonth;
            $startingDayOfWeek = $firstDayOfMonth->dayOfWeek;
            $nomeMes = ucfirst($firstDayOfMonth->locale('pt_PT')->monthName);
        @endphp
        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; width: 100%; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            
            <!-- Cabeçalho do Calendário com botões compactos -->
            <div style="background: #1f2937; color: #ffffff; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <button wire:click="mudarMes(-1)" type="button" style="background: #374151; color: #ffffff; border: none; padding: 10px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px;">◀</button>
                <h2 style="font-size: 18px; font-weight: 900; text-transform: uppercase; margin: 0; color: #ffffff; letter-spacing: 1px;">{{ $nomeMes }} de {{ $this->currentYear }}</h2>
                <button wire:click="mudarMes(1)" type="button" style="background: #374151; color: #ffffff; border: none; padding: 10px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px;">▶</button>
            </div>
            
            <!-- Dias da Semana — ABREVIADOS -->
            <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); background: #f3f4f6; text-align: center; padding: 12px 0; font-size: 13px; font-weight: 900; color: #374151; text-transform: uppercase; border-bottom: 1px solid #e5e7eb;">
                <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
            </div>
            
            <!-- Grelha dos Dias -->
            <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 8px; padding: 20px; text-align: center;">
                @for ($i = 0; $i < $startingDayOfWeek; $i++)
                    <div></div>
                @endfor
                @for ($dia = 1; $dia <= $daysInMonth; $dia++)
                    @php
                        $treinado = in_array($dia, $this->diasTreinadosNoMes);
                        $ehHoje = ($dia == \Illuminate\Support\Carbon::now()->day && $this->currentMonth == \Illuminate\Support\Carbon::now()->month && $this->currentYear == \Illuminate\Support\Carbon::now()->year);
                    @endphp
                    <div style="aspect-ratio: 2.2; display: flex; flex-direction: row; gap: 6px; align-items: center; justify-content: center; border-radius: 10px; font-size: 15px; 
                        {{ $treinado ? 'background: #10b981; color: #ffffff; font-weight: 900;' : 'background: #f9fafb; color: #1f2937; font-weight: 600; border: 1px solid #e5e7eb;' }}
                        {{ $ehHoje && !$treinado ? 'border: 2px solid #f59e0b;' : '' }}">
                        
                        <span>{{ $dia }}</span>
                        @if($treinado)
                            <span style="font-size: 12px;">✅</span>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
        
        <!-- 2. CARDS DE ESTATÍSTICAS -->
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; width: 100%;">
            
            <!-- Total Geral -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase;">Total Geral</div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
                    <span style="font-size: 24px; font-weight: 900; color: #111827;">{{ $this->totalGeral }}</span>
                    <span style="font-size: 10px; font-weight: 700; background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 6px;">🏋️‍♂️ Treinos</span>
                </div>
            </div>
            
            <!-- Total Mensal -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase;">Este Mês</div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
                    <span style="font-size: 24px; font-weight: 900; color: #d97706;">{{ $this->totalMensal }}</span>
                    <span style="font-size: 10px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 6px;">📅 Dias</span>
                </div>
            </div>
            
            <!-- Total Semanal -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase;">Esta Semana</div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
                    <span style="font-size: 24px; font-weight: 900; color: #2563eb;">{{ $this->totalSemanal }}</span>
                    <span style="font-size: 10px; font-weight: 700; background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 6px;">⚡ Ativo</span>
                </div>
            </div>
            
            <!-- Sequência -->
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase;">Sequência</div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
                    <span style="font-size: 24px; font-weight: 900; color: #ea580c;">{{ $this->sequenciaDias }} <span style="font-size: 12px;">dias</span></span>
                    <span style="font-size: 10px; font-weight: 700; background: #ffedd5; color: #9a3412; padding: 2px 6px; border-radius: 6px;">🔥 Fogo</span>
                </div>
            </div>
        </div>
        
        <!-- 3. BOTÃO DE ATALHO PARA O MODO TREINO -->
        <div style="background: linear-gradient(to right, #059669, #0d9488); padding: 16px; border-radius: 14px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div>
                <h3 style="font-size: 16px; font-weight: 900; margin: 0; color: #ffffff;">Pronto para treinar hoje? 💪</h3>
                <p style="font-size: 12px; margin: 4px 0 0 0; color: #d1fae5;">Aceda ao Modo Treino para registar os exercícios e cronometrar o descanso.</p>
            </div>
            <a href="{{ \App\Filament\Admin\Pages\ExecutarTreino::getUrl() }}" style="background: #ffffff; color: #065f46; font-weight: 900; font-size: 13px; padding: 10px 18px; border-radius: 10px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap;">
                🚀 Abrir Modo Treino
            </a>
        </div>
    </div>
</x-filament-widgets::widget>