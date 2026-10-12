@php
    $exercicio = $getRecord();
    $temFoto   = $exercicio && $exercicio->foto_aparelho;
@endphp

<div wire:ignore
     class="space-y-4 p-4 border rounded-lg bg-gray-50"
     x-data="{
        exercicioId: @js($exercicio->id ?? null),
        fotoSalva: @js($temFoto ? '/storage/' . $exercicio->foto_aparelho : null),
        previa: null,
        arquivo: null,
        enviando: false,
        msg: '',
        tipo: '',

        escolher(event) {
            const arquivo = event.target.files[0];
            if (!arquivo) return;
            this.arquivo = arquivo;
            this.previa = URL.createObjectURL(arquivo);
            this.msg = '';
        },

        async salvar() {
            if (!this.arquivo) {
                this.msg = 'Escolha uma foto primeiro.';
                this.tipo = 'erro';
                return;
            }

            const dados = new FormData();
            dados.append('exercicio_id', this.exercicioId);
            dados.append('foto', this.arquivo);

            this.enviando = true;
            this.msg = 'Enviando foto...';
            this.tipo = 'info';

            try {
                const resp = await fetch('/admin/salvar-foto-aparelho', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @js(csrf_token()),
                        'Accept': 'application/json'
                    },
                    body: dados
                });

                if (resp.status === 401 || resp.status === 419) {
                    this.msg = '❌ Sessão expirada. Recarregue a página e faça login novamente.';
                    this.tipo = 'erro';
                    return;
                }

                const json = await resp.json();

                if (resp.ok) {
                    this.fotoSalva = json.url + '?t=' + Date.now();
                    this.previa = null;
                    this.arquivo = null;
                    this.$refs.input.value = '';
                    this.msg = '✅ ' + json.message;
                    this.tipo = 'sucesso';
                } else if (resp.status === 422 && json.errors) {
                    this.msg = '❌ ' + Object.values(json.errors)[0][0];
                    this.tipo = 'erro';
                } else {
                    this.msg = '❌ ' + (json.message || 'Erro ao salvar a foto.');
                    this.tipo = 'erro';
                }
            } catch (e) {
                this.msg = '❌ Erro de conexão ou resposta inválida do servidor.';
                this.tipo = 'erro';
            } finally {
                this.enviando = false;
            }
        }
     }">

    {{-- MENSAGEM --}}
    <div x-show="msg" x-text="msg" style="display:none;"
         class="px-4 py-3 rounded-lg font-bold border"
         :class="{
            'bg-green-50 border-green-300 text-green-800': tipo === 'sucesso',
            'bg-red-50 border-red-300 text-red-800': tipo === 'erro',
            'bg-gray-50 border-gray-300 text-gray-700': tipo === 'info'
         }"></div>

    {{-- ÁREA DE FOTO --}}
    <div class="rounded-lg overflow-hidden border-2 border-gray-200 bg-white p-4 text-center min-h-48">

        {{-- Pré-visualização (ainda não salva) --}}
        <div x-show="previa" style="display:none;">
            <img :src="previa" alt="Pré-visualização"
                 class="w-full max-h-72 object-contain mx-auto rounded-lg">
            <p class="text-sm text-amber-600 font-bold mt-3">⚠️ Ainda não salva. Clique em "Salvar foto".</p>
        </div>

        {{-- Foto salva --}}
        <div x-show="!previa && fotoSalva" style="display:none;">
            <img :src="fotoSalva" alt="Foto do aparelho"
                 class="w-full max-h-72 object-contain mx-auto rounded-lg">
            <p class="text-sm text-green-600 font-bold mt-3">✅ Foto salva</p>
        </div>

        {{-- Sem foto --}}
        <div x-show="!previa && !fotoSalva">
            <div class="text-3xl mb-2">📷</div>
            <p class="text-gray-600 font-medium">Escolha uma foto para visualizar</p>
        </div>
    </div>

    {{-- ENVIO --}}
    @if($exercicio && $exercicio->id)
        <div class="space-y-3 mt-4">
            <div class="p-3 bg-white border rounded-lg">
                <label class="block text-sm font-bold text-gray-700 mb-2">Escolha a foto:</label>
                <input type="file"
                       x-ref="input"
                       accept="image/*"
                       x-on:change="escolher($event)"
                       class="block w-full text-sm text-gray-800">
            </div>

            <button type="button"
                    x-on:click="salvar()"
                    x-bind:disabled="enviando"
                    :style="{ opacity: enviando ? 0.6 : 1 }"
                    style="width:100%; padding:12px; background-color:#16a34a; color:white; font-weight:bold; border:none; border-radius:8px; font-size:16px; cursor:pointer;">
                💾 SALVAR FOTO
            </button>
        </div>
    @else
        <p class="text-sm text-gray-600">Salve o exercício primeiro para poder enviar a foto.</p>
    @endif
</div>