<?php

namespace App\Models\Pharmakon;

use CodeIgniter\Model;

class TblPF extends Model
{
    protected $DBGroup          = 'pharmakon';
    protected $table            = 'TblPF';
    protected $primaryKey       = 'InscricaoPFId';
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
            'SELECT TOP 1 InscricaoPFId AS inscricao, NomePF AS nome
             FROM TblPF WHERE InscricaoPFId = ?',
            [$inscricao]
        )->getRowArray();
    }

    public function getPF($InscricaoPFId = null, $Cpf = null, $NomePF = null)
    {
        $sql = <<<SQL
            SELECT 
                TOP 20
                pf.InscricaoPFId
                ,pf.NomePF
                ,pfcom.Cpf
                ,pf.Endereco
                ,pf.Cep
                ,'Fiscal' AS TipoPF
                ,rb.Bairro 
                ,rc.Cidade
                ,rc.Uf
                ,rscp.StatusCadastralId 
                ,rscp.StatusCadastral
            FROM tblPF pf
            LEFT JOIN tblPFComplemento pfcom ON (pf.InscricaoPFId=pfcom.InscricaoPFId)
            LEFT JOIN refBairro rb ON (pf.BairroId =rb.BairroId)
            LEFT JOIN refCidade rc ON (rb.CidadeId=rc.CidadeId)
            LEFT JOIN refStatusCadastralPF rscp ON (pf.StatusCadastralId =rscp.StatusCadastralId)
            WHERE 1=1
SQL;

        $params = [];

        if (!is_null($InscricaoPFId) && !empty($InscricaoPFId)) {
            $sql .= " AND pf.InscricaoPFId = ?";
            $params[] = (int) $InscricaoPFId;
        }

         if (!is_null($Cpf) && !empty($Cpf)) {
            $sql .= " AND pfcom.Cpf = ?";
            $params[] = somenteNumeros($Cpf);
        }

        if (!is_null($NomePF) && !empty($NomePF)) {

            $sql .= " AND pf.NomePF COLLATE Latin1_General_CI_AI LIKE ?";
            $params[] = "%" . $NomePF . "%";
        }

        $sql .= " ORDER BY pf.NomePF ASC";

        $query = $this->db->query($sql, $params);

        return $query->getResultArray();
    }

    public function selectBuscarPF($termo = null, $valor = null, $page = 1): array
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
            if ($termo === 'InscricaoPFId') {
                if ($temEspacoFinal) {
                    $where = ' AND pf.InscricaoPFId = ? ';
                    $params[] = (int) $valor;
                } else {
                    $where = ' AND CAST(pf.InscricaoPFId AS VARCHAR(20)) LIKE ? ';
                    $params[] = $valor . '%';
                }
            } elseif ($termo === 'NomePF') {
                $tokens = preg_split('/\s+/', $valor, -1, PREG_SPLIT_NO_EMPTY);

                if (!empty($tokens)) {
                    $conditions = [];

                    foreach ($tokens as $token) {
                        $conditions[] = 'pf.NomePF COLLATE Latin1_General_CI_AI LIKE ?';
                        $params[] = '%' . $token . '%';
                    }

                    $where = ' AND ' . implode(' AND ', $conditions) . ' ';
                }
            } elseif ($termo === 'Cpf') {
                $where = ' AND pfcom.Cpf LIKE ? ';
                $params[] = somenteNumeros($valor) . '%';
            } else {
                $where = ' AND 1 = 0 ';
            }
        }

        $sql = <<<SQL
            SELECT *
            FROM (
                SELECT
                    ROW_NUMBER() OVER (ORDER BY pf.NomePF ASC, pf.InscricaoPFId ASC) AS RowNum
                    ,pf.InscricaoPFId as id
                    ,pf.NomePF as text
                    ,pf.InscricaoPFId
                    ,pf.NomePF
                    ,pfcom.Cpf
                    ,pf.Endereco
                    ,rb.Bairro
                    ,rc.Cidade
                    ,rc.Uf
                    ,rscp.StatusCadastralId
                    ,rscp.StatusCadastral
                FROM tblPF pf
                LEFT JOIN tblPFComplemento pfcom ON (pf.InscricaoPFId=pfcom.InscricaoPFId)
                LEFT JOIN refBairro rb ON (pf.BairroId=rb.BairroId)
                LEFT JOIN refCidade rc ON (rb.CidadeId=rc.CidadeId)
                LEFT JOIN refStatusCadastralPF rscp ON (pf.StatusCadastralId=rscp.StatusCadastralId)
                WHERE
                    1=1
                    AND pf.NomePF IS NOT NULL
                    AND pf.NomePF <> ''
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

    public function getContatos($InscricaoPFId = null): array
    {
        if (empty($InscricaoPFId)) {
            return [];
        }

        return $this->db->query(
            'SELECT Numero AS Telefone, TipoContatoId FROM tblPFContato WHERE TipoContatoId IN (1,2,3,4,5) AND Numero IS NOT NULL AND InscricaoPFId = ?',
            [(int) $InscricaoPFId]
        )->getResultArray();
    }

    public function getInscricaoPFIdByFuncionarioId(int $funcionarioId): ?int
    {
        $result = $this->db->query('SELECT NumCrf FROM tblFuncionario WHERE FuncionarioId = ?', [$funcionarioId])->getRowArray();
        return $result ? (int) $result['NumCrf'] : null;
    }

    public function getRegioesDoFiscal(int $funcionarioId): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT r.RegiaoFiscalizacaoId AS regiaofiscalizacaoid, r.Regiao AS regiao
            FROM tblFiscalRegiaoFiscalizacao f
            JOIN refRegiaoFiscalizacao r ON r.RegiaoFiscalizacaoId = f.RegiaoFiscalizacaoId
            WHERE f.FuncionarioId = ? AND f.DataSaida IS NULL
            ORDER BY r.RegiaoFiscalizacaoId ASC
        SQL;

        return $this->db->query($sql, [$funcionarioId])->getResultArray();
    }


}
