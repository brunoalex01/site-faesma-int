<?php
/**
 * FAESMA - Remote Database Sync Mapping
 * 
 * Gerencia mapeamento entre campos da view remota (site.cursos_site)
 * e a tabela local (cursos) com sincronização automática de dados
 * 
 * @package FAESMA
 * @version 1.0
 */

class RemoteSyncMapping
{
    /**
     * Mapeamento de campos: remote_field => local_field
     * @var array
     */
    private static $fieldMapping = [
        // Identificadores
        'id' => 'cod_externo',                    // ID único do curso remoto (campo: id)
        'codigo_curso' => 'cd_oferta',            // Código da oferta
        
        // Informações básicas
        'nome' => 'nome',                         // Nome do curso (campo: nome)
        'descricao' => 'descricao_curta',         // Descrição curta
        'descricao_detalhada' => 'descricao_completa', // Descrição longa
        
        // Estrutura curricular
        'duracao_meses' => 'duracao_meses',       // Duração em meses
        'duracao_texto' => 'duracao_texto',       // Texto da duração (ex: "4 anos")
        'carga_horaria' => 'carga_horaria',       // Carga horária total
        
        // Conteúdo do curso
        'objetivos' => 'objetivos',               // Objetivos do curso
        'perfil_egresso' => 'perfil_egresso',     // Perfil do egresso
        'mercado_trabalho' => 'mercado_trabalho', // Mercado de trabalho
        'publico_alvo' => 'publico_alvo',         // Público-alvo
        
        // Informações financeiras e administrativas
        'valor_mensalidade' => 'valor_mensalidade', // Valor da mensalidade
        'vagas_disponiveis' => 'vagas_disponiveis',  // Vagas disponíveis
        
        // Responsável
        'coordenador_nome' => 'coordenador',      // Nome do coordenador
        
        // Imagens e mídia
        'imagem_url' => 'imagem_destaque',        // URL da imagem de destaque
        
        // Avaliação
        'nota_mec' => 'nota_mec',                 // Nota do MEC
        
        // Informações adicionais
        'tcc_obrigatorio' => 'tcc_obrigatorio',   // TCC obrigatório
        'inscricao_online' => 'inscricao_online', // Inscrição online ativa
        'link_oferta' => 'link_oferta',           // Link da oferta
        
        // Status
        'status_remoto' => 'status',              // Status (ativo/inativo)
    ];

    /**
     * Campos que requerem transformação especial
     * @var array
     */
    private static $transformations = [
        // Mapeamento de status remoto para local
        'status' => [
            'ativo' => 'ativo',
            'inativo' => 'inativo',
            'breve' => 'breve',
            'draft' => 'inativo',
        ],
        
        // Conversão de valores booleanos
        'tcc_obrigatorio' => 'boolean',
        'inscricao_online' => 'boolean',
    ];

    /**
     * Get field mapping array
     * 
     * @return array
     */
    public static function getMapping()
    {
        return self::$fieldMapping;
    }

    /**
     * Map remote field name to local field name
     * 
     * @param string $remoteField Remote field name
     * @return string|null Local field name or null if not mapped
     */
    public static function mapField($remoteField)
    {
        return self::$fieldMapping[$remoteField] ?? null;
    }

    /**
     * Get mapped fields for database query construction
     * 
     * @return array Array with structure [remote_field => local_field]
     */
    public static function getMappedFields()
    {
        return array_flip(self::$fieldMapping);
    }

    /**
     * Transform/convert a value based on field type
     * 
     * @param string $field Field name
     * @param mixed $value Value to transform
     * @return mixed Transformed value
     */
    public static function transformValue($field, $value)
    {
        // Se campo não precisa de transformação, retorna valor original
        if (!isset(self::$transformations[$field])) {
            return $value;
        }

        $transformation = self::$transformations[$field];

        // Transformação booleana
        if ($transformation === 'boolean') {
            // Converter string vazia, NULL ou whitespace para 0 (false)
            if ($value === '' || $value === null || (is_string($value) && trim($value) === '')) {
                return 0;
            }
            // Converter true/1/"1"/"yes"/"true" para 1 (true)
            // Converter false/0/"0"/"no"/"false" para 0 (false)
            $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return ($boolValue === null) ? 0 : (int)$boolValue;
        }

        // Transformação de mapeamento (ex: status)
        if (is_array($transformation)) {
            $key = strtolower(trim($value));
            return $transformation[$key] ?? $value;
        }

        return $value;
    }

    /**
     * Validate remote data structure
     * Verifica se a linha remota tem campos obrigatórios
     * 
     * @param array $remoteRow Linha de dados remota
     * @return array [valid => bool, errors => string[]]
     */
    public static function validateRemoteData($remoteRow)
    {
        $errors = [];

        // Campos obrigatórios mínimos
        $requiredFields = [
            'id' => 'ID do curso',
            'nome' => 'Nome do curso',
        ];

        foreach ($requiredFields as $field => $label) {
            // Verificar se campo existe e não é vazio/NULL/0
            $value = $remoteRow[$field] ?? null;
            
            if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                $errors[] = "Campo obrigatório ausente: {$label} ({$field})";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Convert remote row to local database format
     * 
     * @param array $remoteRow Dados da view remota
     * @return array Array pronto para inserir/atualizar no banco local
     */
    public static function convertRemoteToLocal($remoteRow)
    {
        $localData = [];

        // Mapear campos com transformação automática
        foreach (self::$fieldMapping as $remoteField => $localField) {
            if (isset($remoteRow[$remoteField])) {
                $value = $remoteRow[$remoteField];
                // Aplicar transformação se necessária
                $value = self::transformValue($localField, $value);
                $localData[$localField] = $value;
            }
        }

        // Gerar slug se não existir
        if (isset($localData['nome']) && !isset($localData['slug'])) {
            $localData['slug'] = self::generateSlug($localData['nome']);
        }

        // Garantir que categoria e modalidade existam
        // Se não informadas, usar padrões
        if (!isset($localData['category_id'])) {
            $localData['category_id'] = 1; // Graduação (padrão)
        }
        if (!isset($localData['modality_id'])) {
            $localData['modality_id'] = 1; // Primeiro modalidade padrão
        }

        return $localData;
    }

    /**
     * Gera slug a partir de texto
     * 
     * @param string $text Texto para gerar slug
     * @return string Slug
     */
    private static function generateSlug($text)
    {
        // Primeiro, converte para minúsculas
        $text = strtolower($text);
        
        // Remove acentos usando transliteração
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        
        // Remove caracteres especiais, mantendo apenas letras, números e hífens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Remove traços no início e fim
        $text = trim($text, '-');

        return $text;
    }

    /**
     * Build UPDATE query para sincronizar curso existente
     * 
     * @param array $localData Dados mapeados
     * @param int $courseId ID do curso local
     * @return array [sql => string, params => array]
     */
    public static function buildUpdateQuery($localData, $courseId)
    {
        // Campos que não devem ser atualizados
        $protectedFields = ['id', 'slug', 'created_at'];

        $fields = [];
        $params = [];
        $index = 0;

        foreach ($localData as $field => $value) {
            if (!in_array($field, $protectedFields)) {
                $fields[] = "`{$field}` = :{$field}";
                $params[":{$field}"] = $value;
                $index++;
            }
        }

        if (empty($fields)) {
            return [
                'sql' => '',
                'params' => [],
            ];
        }

        $sql = "UPDATE courses SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
        $params[':id'] = $courseId;

        return [
            'sql' => $sql,
            'params' => $params,
        ];
    }

    /**
     * Build INSERT query para novo curso
     * 
     * @param array $localData Dados mapeados
     * @return array [sql => string, params => array]
     */
    public static function buildInsertQuery($localData)
    {
        // Garantir campos obrigatórios
        $required = ['nome', 'slug', 'category_id', 'modality_id'];
        foreach ($required as $field) {
            if (!isset($localData[$field]) || empty($localData[$field])) {
                throw new Exception("Campo obrigatório ausente: {$field}");
            }
        }

        $fields = [];
        $placeholders = [];
        $params = [];

        foreach ($localData as $field => $value) {
            $fields[] = "`{$field}`";
            $placeholders[] = ":{$field}";
            $params[":{$field}"] = $value;
        }

        $sql = "INSERT INTO courses (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

        return [
            'sql' => $sql,
            'params' => $params,
        ];
    }
}
