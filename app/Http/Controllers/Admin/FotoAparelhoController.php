<?php

namespace App\Http\Controllers\Admin;

use App\Models\Exercicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoAparelhoController
{
    public function store(Request $request)
    {
        $request->validate([
            'exercicio_id' => 'required|exists:exercicios,id',
            'foto'         => 'required|image|max:10240',
        ]);

        $exercicio = Exercicio::findOrFail($request->exercicio_id);
        $arquivo   = $request->file('foto');

        if (!$arquivo || !$arquivo->isValid()) {
            return response()->json(['message' => 'Nenhum arquivo válido recebido.'], 422);
        }

        $nomeArquivo = 'foto_' . $exercicio->id . '_' . time() . '.' . $arquivo->getClientOriginalExtension();

        try {
            $caminhoSalvo = $arquivo->storeAs('aparelhos', $nomeArquivo, 'public');

            if (!$caminhoSalvo) {
                throw new \RuntimeException('storeAs retornou false (sem permissão de escrita em storage/app/public).');
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => config('app.debug')
                    ? 'Erro ao gravar: ' . $e->getMessage()
                    : 'Não foi possível gravar o arquivo. Verifique as permissões da pasta storage.',
            ], 500);
        }

        // Apaga a foto antiga, se existir
        if ($exercicio->foto_aparelho) {
            Storage::disk('public')->delete($exercicio->foto_aparelho);
        }

        $exercicio->foto_aparelho = $caminhoSalvo;
        $exercicio->save();

        return response()->json([
            'message' => 'Foto salva com sucesso!',
            'url'     => '/storage/' . $caminhoSalvo,
        ]);
    }
}