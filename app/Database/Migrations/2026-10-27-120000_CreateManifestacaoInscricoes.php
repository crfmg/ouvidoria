<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Permite relacionar uma manifestação a várias inscrições de PF e de PJ.
 */
class CreateManifestacaoInscricoes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'manifestacao_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['PF', 'PJ'],
            ],
            'inscricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('manifestacao_id');
        $this->forge->addUniqueKey(['manifestacao_id', 'tipo', 'inscricao'], 'uq_manifestacao_tipo_inscricao');
        $this->forge->addForeignKey('manifestacao_id', 'manifestacoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('manifestacao_inscricoes');

        // Preserva os estabelecimentos cadastrados antes desta relação existir.
        $this->db->query(
            "INSERT INTO manifestacao_inscricoes (manifestacao_id, tipo, inscricao, created_at)
             SELECT id, 'PJ', CAST(inscricao_pj_id AS CHAR), NOW()
             FROM manifestacoes
             WHERE inscricao_pj_id IS NOT NULL"
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('manifestacao_inscricoes');
    }
}
