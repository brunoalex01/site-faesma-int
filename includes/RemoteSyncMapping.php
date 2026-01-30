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
        'texto_apos_banner' => 'descricao_completa', // Descrição longa
        
        // Estrutura curricular
        'duracao_meses' => 'duracao_meses',       // Duração em meses
        'duracao' => 'duracao_texto',       // Texto da duração (ex: "4 anos")
        'carga_horaria' => 'carga_horaria',       // Carga horária total
        
        // Conteúdo do curso
        'objetivos' => 'objetivos',               // Objetivos do curso
        'perfil_egresso' => 'perfil_egresso',     // Perfil do egresso
        'mercado_texto' => 'mercado_trabalho', // Mercado de trabalho
        'mercado_remuneracao_media' => 'mercado_remuneracao_media', // Remuneração do mercado de trabalho
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
        'link_oferta' => 'link_oferta',           // Link da oferta
        
        // Categoria - NR_GRAU define a categoria
        'nr_grau' => 'category_id',               // 3=Graduação, 4=Pós-Graduação
        
        // Status - inscricao_online define se o curso está ativo
        'inscricao_online' => 'status',           // S=ativo, N=inativo
    ];

    /**
     * Campos que requerem transformação especial
     * @var array
     */
    private static $transformations = [
        // Conversão de valores booleanos
        'tcc_obrigatorio' => 'boolean',
        
        // Mapeamento de NR_GRAU para category_id
        // 3 = Graduação (id=3), 4 = Pós-Graduação (id=4)
        'category_id' => 'nr_grau_to_category',
        
        // Mapeamento de inscricao_online para status
        // S = ativo, N = inativo
        'status' => 'inscricao_online_to_status',
    ];

    /**
     * Campos numéricos (DECIMAL, INT, FLOAT) que devem ser NULL quando vazios
     * @var array
     */
    private static $numericFields = [
        'nota_mec',
        'valor_mensalidade',
        'carga_horaria',
        'duracao_meses',
        'vagas_disponiveis',
        'mercado_remuneracao_media',
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

        // Transformação de NR_GRAU para category_id
        // 3 = Graduação (category_id=3), 4 = Pós-Graduação (category_id=4)
        if ($transformation === 'nr_grau_to_category') {
            $grau = (int) $value;
            // NR_GRAU coincide com category_id no banco atual
            // 3 = Graduação, 4 = Pós-Graduação
            if ($grau === 3 || $grau === 4) {
                return $grau;
            }
            // Valor padrão: Graduação
            return 3;
        }

        // Transformação de inscricao_online para status
        // S = ativo, N = inativo
        if ($transformation === 'inscricao_online_to_status') {
            $inscricao = strtoupper(trim($value ?? ''));
            if ($inscricao === 'S') {
                return 'ativo';
            }
            return 'inativo';
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

        // Sanitizar campos numéricos - converter string vazia para NULL
        foreach (self::$numericFields as $numericField) {
            if (array_key_exists($numericField, $localData)) {
                $localData[$numericField] = self::sanitizeNumericValue($localData[$numericField]);
            }
        }

        // Gerar slug se não existir
        if (isset($localData['nome']) && !isset($localData['slug'])) {
            $localData['slug'] = self::generateSlug($localData['nome']);
        }

        // Garantir que categoria exista (padrão: Graduação = 3)
        if (!isset($localData['category_id']) || empty($localData['category_id'])) {
            $localData['category_id'] = 3; // Graduação (padrão)
        }
        
        // Garantir que modalidade exista
        if (!isset($localData['modality_id']) || empty($localData['modality_id'])) {
            $localData['modality_id'] = 1; // Primeiro modalidade padrão
        }
        
        // Garantir que status exista (padrão: inativo)
        if (!isset($localData['status']) || empty($localData['status'])) {
            $localData['status'] = 'inativo';
        }

        return $localData;
    }

    /**
     * Sanitiza valor numérico - converte string vazia para NULL
     * 
     * @param mixed $value Valor a sanitizar
     * @return mixed Valor numérico ou NULL
     */
    private static function sanitizeNumericValue($value)
    {
        // Se for NULL, manter NULL
        if ($value === null) {
            return null;
        }
        
        // Se for string vazia ou apenas whitespace, retornar NULL
        if (is_string($value) && trim($value) === '') {
            return null;
        }
        
        // Se for um valor numérico válido, retornar como está
        if (is_numeric($value)) {
            return $value;
        }
        
        // Caso contrário, retornar NULL
        return null;
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
