<?php
/**
 * FAESMA - Curriculum Sync Mapping
 * 
 * Gerencia mapeamento entre campos da view remota (site.disciplinas_curso_site)
 * e a tabela local (course_curriculum) com sincronização automática de dados
 * 
 * @package FAESMA
 * @version 1.0
 */

class CurriculumSyncMapping
{
    /**
     * Mapeamento de campos: remote_field => local_field
     * View remota: disciplinas_curso_site
     * Tabela local: course_curriculum
     * 
     * Estrutura da view remota:
     * - disciplina_nome (varchar 150) - Nome da disciplina
     * - id (varchar 100) - ID do curso remoto (= cod_externo no banco local)
     * - curso_nome (varchar 255) - Nome do curso
     * - cod_externo (varchar 15) - Código externo do curso (sigla)
     * - carga_horaria (float) - Carga horária da disciplina
     * - duracao (varchar 100) - Duração do curso (ex: "2 anos | 1.600 horas")
     * - modalidade (varchar 255) - Modalidade do curso (EAD, Presencial, etc)
     *                              -> Corresponde a course_modalities.nome no banco local
     * - modulo (varchar 255) - Módulo/Semestre (ex: "Modulo 1", "Modulo 2")
     * 
     * Tabela local course_curriculum:
     * - id, course_id, semestre, disciplina, carga_horaria, ementa, ordem
     * - duracao, modalidade, cod_externo_remoto, id_curso_remoto, curso_nome_remoto (NOVOS)
     * - created_at, updated_at
     * 
     * Relacionamento de modalidade:
     * - view.modalidade -> course_modalities.nome (tabela local)
     * - O curso já tem modality_id que referencia course_modalities
     * 
     * @var array
     */
    private static $fieldMapping = [
        // Campos principais (existem na tabela local course_curriculum)
        'disciplina_nome' => 'disciplina',        // Nome da disciplina
        'carga_horaria' => 'carga_horaria',       // Carga horária
        'modulo' => 'semestre',                   // Módulo -> Semestre (convertido)
        
        // Novos campos - armazenados diretamente na tabela course_curriculum
        'duracao' => 'duracao',                   // Duração do curso
        'modalidade' => 'modalidade',             // Modalidade (EAD, Presencial, etc)
        'cod_externo' => 'cod_externo_remoto',    // Código externo do curso (sigla)
        'id' => 'id_curso_remoto',                // ID do curso remoto
        'curso_nome' => 'curso_nome_remoto',      // Nome do curso (para referência)
    ];

    /**
     * Campos que requerem transformação especial
     * @var array
     */
    private static $transformations = [
        // Módulo para semestre numérico
        'semestre' => 'modulo_to_semestre',
        
        // Carga horária para inteiro
        'carga_horaria' => 'float_to_int',
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
     * Transform/convert a value based on field type
     * 
     * @param string $field Field name (local)
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

        switch ($transformation) {
            case 'modulo_to_semestre':
                return self::convertModuloToSemestre($value);
                
            case 'float_to_int':
                return self::convertFloatToInt($value);
                
            default:
                return $value;
        }
    }

    /**
     * Converte texto de módulo para número de semestre
     * Ex: "Modulo 1" -> 1, "Módulo 2" -> 2, "1º Semestre" -> 1
     * 
     * @param string $modulo Texto do módulo
     * @return int Número do semestre
     */
    private static function convertModuloToSemestre($modulo)
    {
        if (empty($modulo)) {
            return 1; // Padrão
        }
        
        // Extrair números do texto
        preg_match('/(\d+)/', $modulo, $matches);
        
        if (!empty($matches[1])) {
            return (int) $matches[1];
        }
        
        return 1; // Padrão se não encontrar número
    }

    /**
     * Converte float para inteiro
     * 
     * @param mixed $value Valor
     * @return int
     */
    private static function convertFloatToInt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) round((float) $value);
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
            'disciplina_nome' => 'Nome da disciplina',
            'cod_externo' => 'Código externo do curso',
        ];

        foreach ($requiredFields as $field => $label) {
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

        // Garantir ordem padrão
        if (!isset($localData['ordem'])) {
            $localData['ordem'] = 0;
        }

        return $localData;
    }

    /**
     * Gera chave única para identificar disciplina
     * Usada para verificar se disciplina já existe
     * 
     * @param array $localData Dados mapeados
     * @return string Chave única
     */
    public static function generateUniqueKey($localData)
    {
        $courseCode = $localData['cod_externo_remoto'] ?? '';
        $disciplina = $localData['disciplina'] ?? '';
        $semestre = $localData['semestre'] ?? 1;
        
        return md5("{$courseCode}|{$disciplina}|{$semestre}");
    }

    /**
     * Build UPDATE query para atualizar disciplina existente
     * 
     * @param array $localData Dados mapeados
     * @param int $curriculumId ID da disciplina local
     * @return array [sql => string, params => array]
     */
    public static function buildUpdateQuery($localData, $curriculumId)
    {
        // Campos que não devem ser atualizados (apenas campos de sistema)
        $protectedFields = ['id', 'course_id', 'created_at'];

        $fields = [];
        $params = [];

        foreach ($localData as $field => $value) {
            if (!in_array($field, $protectedFields)) {
                $fields[] = "`{$field}` = :{$field}";
                $params[":{$field}"] = $value;
            }
        }

        if (empty($fields)) {
            return [
                'sql' => '',
                'params' => [],
            ];
        }

        $sql = "UPDATE course_curriculum SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
        $params[':id'] = $curriculumId;

        return [
            'sql' => $sql,
            'params' => $params,
        ];
    }

    /**
     * Build INSERT query para nova disciplina
     * 
     * @param array $localData Dados mapeados
     * @param int $courseId ID do curso local
     * @return array [sql => string, params => array]
     */
    public static function buildInsertQuery($localData, $courseId)
    {
        // Todos os campos do mapeamento agora existem na tabela
        // Não é mais necessário remover campos
        
        // Adicionar course_id
        $localData['course_id'] = $courseId;

        // Validar campo obrigatório
        if (!isset($localData['disciplina']) || empty($localData['disciplina'])) {
            throw new Exception("Campo obrigatório ausente: disciplina");
        }

        $fields = [];
        $placeholders = [];
        $params = [];

        foreach ($localData as $field => $value) {
            $fields[] = "`{$field}`";
            $placeholders[] = ":{$field}";
            $params[":{$field}"] = $value;
        }

        $sql = "INSERT INTO course_curriculum (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";

        return [
            'sql' => $sql,
            'params' => $params,
        ];
    }
}
