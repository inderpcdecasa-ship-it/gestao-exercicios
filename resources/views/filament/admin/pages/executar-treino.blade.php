<x-filament-panels::page>
    <div class="max-w-md mx-auto p-4 bg-white dark:bg-gray-900 rounded-2xl shadow-xl space-y-6"
         x-data="{
             ativo: false,
             tempoRestante: 60,
             timer: null,
             telaAcesa: false,
             aoVoltar: null,
             enviandoFoto: false,
             progressoFoto: 0,
             faseBotao: 'registrar', // 'registrar', 'pausa', 'iniciar'

             init() {
                 this.ativarTelaAcesa();
                 this.aoVoltar = () => {
                     if (document.visibilityState === 'visible' && !this.telaAcesa) {
                         this.ativarTelaAcesa();
                     }
                 };
                 document.addEventListener('visibilitychange', this.aoVoltar);
             },
             destroy() {
                 document.removeEventListener('visibilitychange', this.aoVoltar);
                 clearInterval(this.timer);
                 this.liberarTela();
             },

             async ativarTelaAcesa() {
                 try {
                     if (!('wakeLock' in navigator) || document.visibilityState !== 'visible') return;
                     const sentinela = await navigator.wakeLock.request('screen');
                     window.__treinoWakeLock = sentinela;
                     this.telaAcesa = true;
                     sentinela.addEventListener('release', () => { this.telaAcesa = false; });
                 } catch (e) {
                     this.telaAcesa = false;
                 }
             },
             liberarTela() {
                 try {
                     if (window.__treinoWakeLock) window.__treinoWakeLock.release();
                 } catch (e) {}
                 window.__treinoWakeLock = null;
                 this.telaAcesa = false;
             },

             vibrar(padrao) {
                 try {
                     if (navigator.vibrate) navigator.vibrate(padrao);
                 } catch (e) {}
             },
             concluirTreino() {
                 this.pararCronometro();
                 this.liberarTela();
                 this.vibrar([200, 100, 200, 100, 400]);
             },

             async reduzirFoto(arquivo, ladoMaximo = 1600, qualidade = 0.82) {
                 const imagem = await createImageBitmap(arquivo);
                 const escala = Math.min(1, ladoMaximo / Math.max(imagem.width, imagem.height));
                 const largura = Math.round(imagem.width * escala);
                 const altura = Math.round(imagem.height * escala);

                 const canvas = document.createElement('canvas');
                 canvas.width = largura;
                 canvas.height = altura;
                 const ctx = canvas.getContext('2d');
                 ctx.fillStyle = '#ffffff';
                 ctx.fillRect(0, 0, largura, altura);
                 ctx.drawImage(imagem, 0, 0, largura, altura);
                 if (imagem.close) imagem.close();

                 const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', qualidade));
                 if (!blob) throw new Error('Não foi possível reduzir a foto');

                 if (blob.size >= arquivo.size) return arquivo;

                 const nome = (arquivo.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg';
                 return new File([blob], nome, { type: 'image/jpeg' });
             },
             async enviarFoto(event) {
                 const original = event.target.files[0];
                 if (!original) return;

                 this.enviandoFoto = true;
                 this.progressoFoto = 0;

                 let arquivo = original;
                 try {
                     arquivo = await this.reduzirFoto(original);
                 } catch (e) {
                     arquivo = original;
                 }

                 this.$wire.upload(
                     'novaFoto',
                     arquivo,
                     () => { this.enviandoFoto = false; },
                     () => { this.enviandoFoto = false; },
                     (e) => { this.progressoFoto = e.detail.progress; }
                 );
             },

             iniciarCronometro() {
                 this.tempoRestante = 60;
                 this.ativo = true;
                 this.faseBotao = 'pausa';
                 clearInterval(this.timer);
                 this.timer = setInterval(() => {
                     if (this.tempoRestante > 0) {
                         this.tempoRestante--;
                     } else {
                         this.pararCronometro();
                         this.tocarBip();
                         this.faseBotao = 'iniciar';
                     }
                 }, 1000);
             },
             pararCronometro() {
                 this.ativo = false;
                 clearInterval(this.timer);
                 if (this.faseBotao === 'pausa') {
                     this.faseBotao = 'iniciar';
                 }
             },
             tocarBip() {
                 this.vibrar([300, 100, 300, 100, 300]);
                 try {
                     const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                     const osc = audioCtx.createOscillator();
                     const gain = audioCtx.createGain();
                     osc.type = 'sine';
                     osc.frequency.value = 880;
                     gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                     gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.8);
                     osc.connect(gain);
                     gain.connect(audioCtx.destination);
                     osc.start();
                     osc.stop(audioCtx.currentTime + 0.8);
                 } catch (e) {
                     console.log('Áudio não suportado');
                 }
             }
         }"
         @serie-registrada.window="iniciarCronometro()"
         @serie-desfeita.window="pararCronometro(); faseBotao = 'registrar';"
         @treino-concluido.window="concluirTreino()">

        {{-- SETAS DE NAVEGAÇÃO --}}
        <div class="flex items-center justify-between mb-2">
            <button wire:click="exercicioAnterior()" 
                @if($currentIndex === 0) disabled @endif
                class="px-4 py-2 bg-gray-200 rounded-lg font-bold @if($currentIndex === 0) opacity-50 cursor-not-allowed @endif">
                ← Anterior
            </button>

            <div class="text-center">
                @if ($treinoConcluido)
                    <span class="text-green-600 font-bold block">✅ TREINO CONCLUÍDO</span>
                @endif
            </div>

            <button wire:click="proximoExercicio()"
                @if($currentIndex >= count($exercicios) - 1) disabled @endif
                class="px-4 py-2 bg-gray-200 rounded-lg font-bold @if($currentIndex >= count($exercicios) - 1) opacity-50 cursor-not-allowed @endif">
                Próximo →
            </button>
        </div>

        {{-- Aviso: tela mantida ligada --}}
        <p x-show="telaAcesa" style="display: none; text-align: center; font-size: 12px; color: #9ca3af; margin-top: 0;">
            🔆 Tela mantida ligada durante o treino
        </p>

        @if($this->hasExercises())
            @php $exercicio = $this->exercicioAtual; @endphp
            @if($exercicio)
                {{-- Cabeçalho + Botão Reset Exercício --}}
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Exercício {{ $currentIndex + 1 }} de {{ count($exercicios) }}
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400 rounded-full font-bold text-sm shadow-sm">
                            Série {{ min($currentSerie, $exercicio->series) }} / {{ $exercicio->series }}
                        </span>
                        {{-- Botão Reset SÓ Este Exercício --}}
                        <button wire:click="reiniciarExercicioAtual()"
                            wire:confirm="Reiniciar SÓ este exercício? Os outros ficam salvos!"
                            class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-bold hover:bg-yellow-200 transition"
                            title="Recomeçar este exercício do zero">
                            🔄
                        </button>
                    </div>
                </div>

                {{-- Nome do Exercício --}}
                <h2 class="text-2xl font-black text-gray-900 dark:text-white text-center tracking-tight mb-6">
                    {{ $exercicio->nome }}
                </h2>

                {{-- Botão Principal com Estados Dinâmicos (Registrar / Em Pausa / Iniciar) --}}
                @if (!$treinoConcluido && $currentSerie <= $exercicio->series)
                    <button type="button" 
                        @click="
                            if (faseBotao === 'iniciar') {
                                faseBotao = 'registrar';
                            } else if (faseBotao === 'registrar') {
                                $wire.registrarSerie();
                            }
                        "
                        wire:loading.attr="disabled" 
                        wire:target="registrarSerie" 
                        style="color: #ffffff !important;" 
                        :style="
                            faseBotao === 'pausa' ? 'background-color: #d97706 !important;' : 
                            (faseBotao === 'iniciar' ? 'background-color: #2563eb !important;' : 'background-color: #059669 !important;')
                        "
                        class="w-full py-4 px-6 text-xl font-black rounded-xl shadow-lg hover:opacity-95 active:scale-95 transition flex items-center justify-center space-x-2 mb-6">
                        
                        <span x-show="faseBotao === 'registrar'">✅ REGISTRAR SÉRIE {{ $currentSerie }}</span>
                        <span x-show="faseBotao === 'pausa'" style="display: none;">⏸️ EM PAUSA (DESCANSANDO...)</span>
                        <span x-show="faseBotao === 'iniciar'" style="display: none;">▶️ INICIAR PRÓXIMA SÉRIE</span>
                    </button>
                @endif

                {{-- Desfazer a última série registrada (Com espaçamento adequado) --}}
                @if($this->temSerieRegistrada)
                    <div class="text-center mt-4 mb-6">
                        <button type="button"
                                wire:click="desfazerUltimaSerie; faseBotao = 'registrar';"
                                wire:confirm="Desfazer a última série registrada?"
                                wire:loading.attr="disabled"
                                wire:target="desfazerUltimaSerie"
                                class="text-sm font-bold text-gray-500 dark:text-gray-400 hover:underline">
                            ↩️ Desfazer última série
                        </button>
                    </div>
                @endif

                {{-- Cronômetro --}}
                <div x-show="ativo" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-850 border border-amber-200 dark:border-amber-700/50 p-5 rounded-2xl text-center space-y-4 shadow-md mb-6" 
                     style="display: none;">
                    <div class="flex items-center justify-center space-x-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold">⏱️</span>
                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-widest">Tempo de Descanso</span>
                    </div>
                    <div class="text-5xl font-black tracking-tight text-amber-600 dark:text-amber-400 tabular-nums">
                        <span x-text="tempoRestante"></span><span class="text-2xl font-medium text-amber-500/80 ml-1">s</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <button @click="tempoRestante += 15" type="button" style="background-color: #fef3c7 !important; color: #92400e !important;" class="py-2.5 px-3 text-xs font-bold rounded-xl border border-amber-300 shadow-sm hover:opacity-90 transition active:scale-95 flex items-center justify-center">
                            <span>+15s Descanso</span>
                        </button>
                        <button @click="pararCronometro(); faseBotao = 'iniciar';" type="button" style="background-color: #fee2e2 !important; color: #991b1b !important;" class="py-2.5 px-3 text-xs font-bold rounded-xl border border-red-300 shadow-sm hover:opacity-90 transition active:scale-95 flex items-center justify-center">
                            <span>Pular Descanso</span>
                        </button>
                    </div>
                </div>

                {{-- Repetições, Peso e carga do último treino --}}
                <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800/60 p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">Repetições</label>
                        <input type="number" wire:model="repeticoesFeitas" class="w-full text-center text-lg font-bold border-gray-300 dark:border-gray-600 rounded-xl shadow-sm dark:bg-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">Peso (kg)</label>
                        <input type="number" step="0.5" wire:model="pesoUtilizado" class="w-full text-center text-lg font-bold border-gray-300 dark:border-gray-600 rounded-xl shadow-sm dark:bg-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    @if($ultimoTreino)
                        <div class="text-gray-500 dark:text-gray-400" style="grid-column: span 2 / span 2; text-align: center; font-size: 12px;">
                            Último treino{{ $ultimoTreino['data'] ? ' (' . $ultimoTreino['data'] . ')' : '' }}:
                            <strong>{{ $ultimoTreino['resumo'] }}</strong>
                        </div>
                    @endif
                </div>

                {{-- Foto --}}
                @php
                    $chaveFoto = $exercicio->id . '-' . md5((string) $exercicio->foto_aparelho);
                    $temPrevia = $novaFoto && !$errors->has('novaFoto') && $novaFoto->isPreviewable();
                @endphp

                <div class="flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4 border border-gray-200/80 dark:border-gray-700/80 space-y-3">

                    @if($temPrevia)
                        <div class="w-full overflow-hidden rounded-xl shadow-md border-2 border-amber-400">
                            <img src="{{ $novaFoto->temporaryUrl() }}" alt="Pré-visualização" class="w-full h-56 object-cover">
                        </div>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-bold text-center">
                            ⚠️ Ainda não salva. Toque em "Salvar foto".
                        </p>
                    @elseif(!empty($exercicio->foto_aparelho))
                        <div class="w-full overflow-hidden rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
                            <img src="{{ asset('storage/' . $exercicio->foto_aparelho) }}" alt="Aparelho" class="w-full h-56 object-cover">
                        </div>
                    @else
                        <div class="text-center py-4 space-y-3">
                            <div class="mx-auto w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 font-medium">Nenhuma foto cadastrada para este aparelho.</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3 w-full">
                        <label style="color: #ffffff !important; background-color: #f59e0b !important;" class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl text-sm font-bold shadow-md cursor-pointer hover:opacity-90 transition">
                            <span>📸 Tirar foto</span>
                            <input type="file" x-on:change="enviarFoto($event)" wire:key="foto-camera-{{ $chaveFoto }}" accept="image/*" capture="environment" class="hidden">
                        </label>
                        <label style="color: #374151 !important; background-color: #e5e7eb !important;" class="flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl text-sm font-bold shadow-md cursor-pointer hover:opacity-90 transition">
                            <span>🖼️ Galeria</span>
                            <input type="file" x-on:change="enviarFoto($event)" wire:key="foto-galeria-{{ $chaveFoto }}" accept="image/*" class="hidden">
                        </label>
                    </div>

                    <div x-show="enviandoFoto" style="display: none;" class="text-sm text-gray-600 dark:text-gray-300 font-bold text-center">
                        Reduzindo e enviando foto... <span x-text="progressoFoto + '%'"></span> ⏳
                    </div>

                    @error('novaFoto')
                        <div class="w-full text-center bg-red-50 dark:bg-red-950/50 p-3 rounded-xl border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-700 dark:text-red-300 font-bold">❌ {{ $message }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Se a foto for muito grande, tente uma menor.</p>
                        </div>
                    @enderror

                    @if($novaFoto && !$errors->has('novaFoto'))
                        <div class="mt-2 w-full text-center bg-emerald-50 dark:bg-emerald-950/50 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 space-y-3">
                            <span class="text-xs text-emerald-700 dark:text-emerald-300 font-bold block">✨ Foto selecionada!</span>
                            <button wire:click="salvarFoto" wire:loading.attr="disabled" wire:target="salvarFoto" type="button" style="color: #ffffff !important; background-color: #16a34a !important;" class="w-full py-3 px-4 text-sm font-bold rounded-xl shadow-md hover:opacity-95 transition">
                                <span wire:loading.remove wire:target="salvarFoto">💾 Salvar foto no aparelho</span>
                                <span wire:loading wire:target="salvarFoto">Salvando foto... ⏳</span>
                            </button>
                        </div>
                    @endif
                </div>

                @if($exercicio->descricao)
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center italic bg-gray-50 dark:bg-gray-800/40 p-3 rounded-xl mt-4">
                        "{{ $exercicio->descricao }}"
                    </p>
                @endif
            @endif
        @else
            <div class="text-center py-12 space-y-4">
                @if ($treinoConcluido)
                    <h3 class="text-xl font-black text-emerald-600">Treino de Hoje Concluído! 🎉</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Use as setas para revisar ou reinicie cada exercício com o botão 🔄.</p>
                @else
                    <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300">Nenhum exercício encontrado</h3>
                    <p class="text-gray-500 text-sm">Não há exercícios cadastrados para o dia de hoje.</p>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>