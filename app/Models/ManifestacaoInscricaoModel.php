<?php

namespace App\Models;

use CodeIgniter\Model;

class ManifestacaoInscricaoModel extends Model
{
    protected $table            = 'manifestacao_inscricoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['manifestacao_id', 'tipo', 'inscricao', 'created_at'];
    protected $useTimestamps    = false;

    /**
     * @return array{PF: string[], PJ: string[]}
     */
    public function agrupadasPorManifestacao(int $manifestacaoId): array
    {
        $resultado = ['PF' => [], 'PJ' => []];
        $registros = $this->where('manifestacao_id', $manifestacaoId)
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($registros as $registro) {
            $tipo = $registro['tipo'] ?? '';
            if (isset($resultado[$tipo])) {
                $resultado[$tipo][] = (string) $registro['inscricao'];
            }
        }

        return $resultado;
    }

    /**
     * Substitui todas as inscrições de uma manifestação.
     *
     * @param string[] $inscricoesPf
     * @param string[] $inscricoesPj
     */
    public function salvarParaManifestacao(int $manifestacaoId, array $inscricoesPf, array $inscricoesPj): void
    {
        if ($this->where('manifestacao_id', $manifestacaoId)->delete() === false) {
            throw new \RuntimeException('Falha ao limpar as identificações da manifestação.');
        }

        $agora = date('Y-m-d H:i:s');
        $dados = [];
        foreach (['PF' => $inscricoesPf, 'PJ' => $inscricoesPj] as $tipo => $inscricoes) {
            foreach ($inscricoes as $inscricao) {
                $dados[] = [
                    'manifestacao_id' => $manifestacaoId,
                    'tipo'            => $tipo,
                    'inscricao'       => $inscricao,
                    'created_at'      => $agora,
                ];
            }
        }

        if ($dados !== []) {
            if ($this->insertBatch($dados) === false) {
                throw new \RuntimeException('Falha ao salvar as identificações da manifestação.');
            }
        }
    }
}
