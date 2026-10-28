<?php

namespace App\Models\Pharmakon;

use CodeIgniter\Model;

class TblPJ extends Model
{
    protected $DBGroup          = 'pharmakon';
    protected $table            = 'TblPJ';
    protected $primaryKey       = 'InscricaoPJId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function buscarIdentificacao(int $inscricao): ?array
    {
        return $this->db->query(
            'SELECT TOP 1 InscricaoPJId AS inscricao, RazaoSocial AS nome, NomeFantasia AS detalhe
             FROM TblPJ WHERE InscricaoPJId = ?',
            [$inscricao]
        )->getRowArray();
    }

    public function consultarEstabelecimento($InscricaoPJId=null){

        $sql = <<<SQL
                SELECT
                    pj.HorarioInicial
                    ,pj.HorarioFinal 
                    ,pj.RazaoSocial
                    ,pj.NomeFantasia
                    ,pj.InscricaoPJId 
                    ,pj.Endereco
                    ,rb.Bairro
                    ,rc.Cidade
                    ,rc.Uf
                    ,pjC.PerfilAssistenciaId
                    ,tPF.NomePF
                    ,tPF.InscricaoPFId
                    ,pjC.Cgc
                FROM tblPJ pj
                INNER JOIN refBairro rb ON pj.BairroId = rb.BairroID 
                INNER JOIN refCidade rc ON rb.CidadeId = rc.CidadeID
                INNER JOIN tblPJComplemento pjC ON pj.InscricaoPJId = pjC.InscricaoPJId 
                INNER JOIN tblCRT tCRT ON pj.InscricaoPJId = tCRT.InscricaoPJId
                INNER JOIN tblCRTResponsabilidade tCRTR ON tCRT.CRTId = tCRTR.CRTId
                INNER JOIN tblPF tPF ON tCRTR.InscricaoPFId = tPF.InscricaoPFId
                WHERE tCRT.StatusId = 2
                AND pj.InscricaoPJId = ?
SQL;

        $params[] = $InscricaoPJId;

        $query = $this->db->query($sql, $params);

        return $query->getResultArray();
    }

    public function getPJ($InscricaoPJId = null, $Cgc = null, $RazaoSocial = null, $Endereco = null)
    {
        $sql = <<<SQL
            SELECT 
                TOP 20
                pj.InscricaoPJId
                ,pj.RazaoSocial
                ,pj.NomeFantasia
                ,pjcom.Cgc
                ,rtp.TipoPJ
                ,rtp.AtividadePrivativa
                ,pj.Endereco
                ,rb.Bairro 
                ,rc.Cidade
                ,rc.Uf
                ,rscp.StatusCadastralPJId 
                ,rscp.StatusCadastralPJ
            FROM tblPJ pj
            LEFT JOIN refTipoPJ rtp ON (pj.TipoPJId = rtp.TipoPJId)
            LEFT JOIN tblPJComplemento pjcom ON (pj.InscricaoPJId=pjcom.InscricaoPJId)
            LEFT JOIN refBairro rb ON (pj.BairroId =rb.BairroId)
            LEFT JOIN refCidade rc ON (rb.CidadeId=rc.CidadeId)
            LEFT JOIN refStatusCadastralPJ rscp ON (pj.StatusCadastralPJId =rscp.StatusCadastralPJId)
            WHERE 1=1
SQL;

        $params = [];

        if (!is_null($InscricaoPJId) && !empty($InscricaoPJId)) {
            $sql .= " AND pj.InscricaoPJId = ?";
            $params[] = (int) $InscricaoPJId;
        }

        if (!is_null($Cgc) && !empty($Cgc)) {
            $sql .= " AND pjcom.Cgc = ?";
            $params[] = somenteNumeros($Cgc);
        }

        if (!is_null($RazaoSocial) && !empty($RazaoSocial)) {
            $sql .= " AND pj.RazaoSocial COLLATE Latin1_General_CI_AI LIKE ?";
            $params[] = "%" . $RazaoSocial . "%";
        }

        if (!is_null($Endereco) && !empty($Endereco)) {
            $sql .= " AND pj.Endereco COLLATE Latin1_General_CI_AI LIKE ?";
            $params[] = "%" . $Endereco . "%";
        }

        $sql .= " ORDER BY pj.RazaoSocial ASC";

        $query = $this->db->query($sql, $params);

        return $query->getResultArray();
    }

    public function getHorarioFuncionamentoPAF(int $InscricaoPJId): string
    {
        $sqlAnotacao = <<<SQL
            SELECT TOP 1
                Anotacao
            FROM tblPJAnotacao
            WHERE InscricaoPJId = ?
              AND CodTipoAnotacao = 70
              AND DataFim IS NULL
            ORDER BY DataInicio DESC, CodAnotacao DESC
SQL;

        $anotacao = $this->db->query($sqlAnotacao, [$InscricaoPJId])->getRowArray();
        if (!empty($anotacao['Anotacao'])) {
            return trim((string) $anotacao['Anotacao']);
        }

        $sqlHorario = <<<SQL
            SELECT
                HorarioInicial,
                HorarioFinal,
                HorarioInicialSabado,
                HorarioFinalSabado,
                HorarioInicialDomingo,
                HorarioFinalDomingo
            FROM tblPJ
            WHERE InscricaoPJId = ?
SQL;

        $horarios = $this->db->query($sqlHorario, [$InscricaoPJId])->getRowArray();
        if (empty($horarios)) {
            return '-';
        }

        $partes = [];

        $semana = $this->formatarFaixaHorario($horarios['HorarioInicial'] ?? null, $horarios['HorarioFinal'] ?? null);
        if ($semana !== null) {
            $partes[] = 'Seg-Sex: ' . $semana;
        }

        $sabado = $this->formatarFaixaHorario($horarios['HorarioInicialSabado'] ?? null, $horarios['HorarioFinalSabado'] ?? null);
        if ($sabado !== null) {
            $partes[] = 'Sab: ' . $sabado;
        }

        $domingo = $this->formatarFaixaHorario($horarios['HorarioInicialDomingo'] ?? null, $horarios['HorarioFinalDomingo'] ?? null);
        if ($domingo !== null) {
            $partes[] = 'Dom: ' . $domingo;
        }

        return $partes !== [] ? implode(' | ', $partes) : '-';
    }

    public function selectBuscarPJ($termo = null, $valor = null, $page = 1): array
    {
        $where = '';
        $params = [];
        $pageSize = 30;
        $page = max(1, (int) $page);
        $startRow = (($page - 1) * $pageSize) + 1;
        $endRow = $startRow + $pageSize;

        $valorOriginal = (string) ($valor ?? '');
        $valor = trim($valorOriginal);
        $temEspacoFinal = $valorOriginal !== '' && substr($valorOriginal, -1) === ' ';

        if ($termo !== null && $termo !== '' && $valor !== '') {
            if ($termo === 'InscricaoPJId') {
                if ($temEspacoFinal) {
                    $where = ' AND pj.InscricaoPJId = ? ';
                    $params[] = (int) $valor;
                } else {
                    $where = ' AND CAST(pj.InscricaoPJId AS VARCHAR(20)) LIKE ? ';
                    $params[] = $valor . '%';
                }
            } elseif ($termo === 'RazaoSocial') {
                $tokens = preg_split('/\s+/', $valor, -1, PREG_SPLIT_NO_EMPTY);

                if (!empty($tokens)) {
                    $conditions = [];

                    foreach ($tokens as $token) {
                        $conditions[] = 'pj.RazaoSocial COLLATE Latin1_General_CI_AI LIKE ?';
                        $params[] = '%' . $token . '%';
                    }

                    $where = ' AND ' . implode(' AND ', $conditions) . ' ';
                }
            } elseif ($termo === 'Cgc') {
                $where = ' AND pjcom.Cgc LIKE ? ';
                $params[] = somenteNumeros($valor) . '%';
            } else {
                $where = ' AND 1 = 0 ';
            }
        }

        $sql = <<<SQL
            SELECT *
            FROM (
                SELECT
                    ROW_NUMBER() OVER (ORDER BY pj.RazaoSocial ASC, pj.InscricaoPJId ASC) AS RowNum
                    ,pj.InscricaoPJId as id
                    ,pj.RazaoSocial as text
                    ,pj.InscricaoPJId
                    ,pj.RazaoSocial
                    ,pj.NomeFantasia
                    ,pjcom.Cgc
                    ,pj.Endereco
                    ,rb.Bairro
                    ,rc.Cidade
                    ,rc.Uf
                    ,rscp.StatusCadastralPJId
                    ,rscp.StatusCadastralPJ
                FROM tblPJ pj
                LEFT JOIN tblPJComplemento pjcom ON (pj.InscricaoPJId=pjcom.InscricaoPJId)
                LEFT JOIN refBairro rb ON (pj.BairroId=rb.BairroId)
                LEFT JOIN refCidade rc ON (rb.CidadeId=rc.CidadeId)
                LEFT JOIN refStatusCadastralPJ rscp ON (pj.StatusCadastralPJId=rscp.StatusCadastralPJId)
                WHERE
                    1=1
                    $where
            ) AS BuscaPaginada
            WHERE RowNum BETWEEN ? AND ?
            ORDER BY RowNum ASC
SQL;

        $params[] = $startRow;
        $params[] = $endRow;

        $resultados = $this->db->query($sql, $params)->getResultArray();
        $hasMore = count($resultados) > $pageSize;

        if ($hasMore) {
            $resultados = array_slice($resultados, 0, $pageSize);
        }

        return [
            'results' => $resultados,
            'pagination' => [
                'more' => $hasMore,
            ],
        ];
    }

    private function formatarFaixaHorario($inicio, $fim): ?string
    {
        $inicioFmt = $this->formatarHorario($inicio);
        $fimFmt    = $this->formatarHorario($fim);

        if ($inicioFmt === null && $fimFmt === null) {
            return null;
        }

        if ($inicioFmt !== null && $fimFmt !== null) {
            return $inicioFmt . ' as ' . $fimFmt;
        }

        return $inicioFmt ?? $fimFmt;
    }

    private function formatarHorario($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $timestamp = strtotime((string) $valor);
        if ($timestamp === false) {
            return null;
        }

        return date('H\hi', $timestamp);
    }

}
