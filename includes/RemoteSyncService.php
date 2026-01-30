<?php
/**
 * FAESMA - Remote Database Sync Service
 * 
 * Serviço para sincronizar dados da view remota com o banco local
 * Utiliza RemoteSyncMapping para mapeamento automático de campos
 * 
 * @package FAESMA
 * @version 1.0
 */

require_once __DIR__ . '/RemoteSyncMapping.php';
require_once __DIR__ . '/CurriculumSyncMapping.php';
require_once __DIR__ . '/db.php';

class RemoteSyncService
{
    /**
     * PDO connection local
     * @var PDO
     */
    private $localDb;

    /**
     * PDO connection remota
     * @var PDO
     */
    private $remoteDb;

    /**
     * Log de operações
     * @var array
     */
    private $log = [];

    /**
     * Constructor
     * 
     * @param PDO $localDb Conexão local
     * @param PDO $remoteDb Conexão remota
     */
    public function __construct(PDO $localDb, PDO $remoteDb)
    {
        $this->localDb = $localDb;
        $this->remoteDb = $remoteDb;
    }

    /**
     * Sincronizar todos os cursos da view remota
     * 
     * ⚠️ NOVO COMPORTAMENTO: Desativa cursos não encontrados na view
     * - Marca todos os cursos como 'inativo' no início
     * - Sincroniza cursos da view (marcando como 'ativo')
     * - Resultado: Apenas cursos da view remota ficam ativos
     * 
     * @param string $viewName Nome da view remota
     * @param int $limit Limite de registros para sincronizar
     * @return array Resultado da sincronização [sucesso, falha, atualizado, criado, desativado]
     */
    public function syncAllCourses($viewName = 'cursos_site', $limit = 500)
    {
        try {
            // Buscar dados da view remota
            $remoteData = fetchAllFromView($this->remoteDb, $viewName, $limit);

            if (empty($remoteData)) {
                return [
                    'status' => 'erro',
                    'mensagem' => 'Nenhum curso encontrado na view remota',
                    'log' => $this->log,
                ];
            }

            $stats = [
                'criado' => 0,
                'atualizado' => 0,
                'falha' => 0,
                'pulado' => 0,
                'desativado' => 0,
            ];

            $this->log[] = "Iniciando sincronização de " . count($remoteData) . " curso(s)";
            $this->log[] = "⚠️ Todos os cursos não sincronizados serão desativados";

            // PASSO 1: Marcar TODOS os cursos como 'inativo'
            $this->log[] = "[PASSO 1] Marcando todos os cursos como inativo...";
            $deactivateResult = $this->deactivateAllCourses();
            $this->log[] = "[PASSO 1] ✓ Cursos marcados como inativo: " . $deactivateResult;

            // PASSO 2: Sincronizar e REATIVAR apenas os cursos da view
            $this->log[] = "[PASSO 2] Sincronizando cursos da view remota...";
            $syncedCourseIds = [];

            // Sincronizar cada curso
            foreach ($remoteData as $index => $remoteRow) {
                try {
                    $result = $this->syncCourse($remoteRow, true); // true = reativar

                    if ($result['action'] === 'created') {
                        $stats['criado']++;
                        $syncedCourseIds[] = $result['curso_id'];
                        $this->log[] = "[✓ Criado] {$remoteRow['nome']} (ID: {$remoteRow['id']})";
                    } elseif ($result['action'] === 'updated') {
                        $stats['atualizado']++;
                        $syncedCourseIds[] = $result['curso_id'];
                        $this->log[] = "[✓ Atualizado] {$remoteRow['nome']} (ID: {$remoteRow['id']})";
                    } elseif ($result['action'] === 'skipped') {
                        $stats['pulado']++;
                        $this->log[] = "[⊘ Pulado] {$remoteRow['nome']} - {$result['reason']}";
                    }
                } catch (Exception $e) {
                    $stats['falha']++;
                    $this->log[] = "[❌ ERRO] Linha " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // PASSO 3: Contar quantos cursos foram desativados (não sincronizados)
            $this->log[] = "[PASSO 3] Resumo de desativação...";
            $this->log[] = "[✓] Cursos sincronizados (reativados): " . count($syncedCourseIds);
            $stats['desativado'] = $deactivateResult - count($syncedCourseIds);
            $this->log[] = "[✓] Cursos desativados (não encontrados na view): " . $stats['desativado'];

            return [
                'status' => 'sucesso',
                'mensagem' => "Sincronização concluída com desativação de cursos não sincronizados",
                'stats' => $stats,
                'log' => $this->log,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
                'log' => $this->log,
            ];
        }
    }

    /**
     * Desativar todos os cursos do banco local
     * 
     * @return int Número de cursos desativados
     */
    private function deactivateAllCourses()
    {
        try {
            $sql = "UPDATE courses SET status = 'inativo' WHERE status != 'inativo'";
            $stmt = $this->localDb->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            throw new Exception("Erro ao desativar cursos: " . $e->getMessage());
        }
    }

    /**
     * Sincronizar um curso individual
     * 
     * @param array $remoteRow Dados da view remota
     * @param bool $reactivate Se true, reativa o curso (marca como 'ativo')
     * @return array [action => 'created'|'updated'|'skipped', curso_id => int]
     */
    public function syncCourse($remoteRow, $reactivate = false)
    {
        // Validar dados
        $validation = RemoteSyncMapping::validateRemoteData($remoteRow);
        if (!$validation['valid']) {
            // Log mais detalhado do erro
            $id = $remoteRow['id'] ?? 'SEM_ID';
            $nome = $remoteRow['nome'] ?? 'SEM_NOME';
            $errorMsg = implode("; ", $validation['errors']);
            $this->log[] = "[PULADO] ID: {$id}, Nome: {$nome} - {$errorMsg}";
            throw new Exception(implode("; ", $validation['errors']));
        }

        // Converter para formato local
        $localData = RemoteSyncMapping::convertRemoteToLocal($remoteRow);
        
        // Se reactivate é true, forçar status = 'ativo'
        if ($reactivate) {
            $localData['status'] = 'ativo';
        }

        // Verificar se curso já existe (buscar por cod_externo ou slug)
        $existingCourse = $this->findExistingCourse($localData);

        if ($existingCourse) {
            // Atualizar curso existente
            return $this->updateCourse($existingCourse['id'], $localData);
        } else {
            // Criar novo curso
            return $this->createCourse($localData);
        }
    }

    /**
     * Encontrar curso existente no banco local
     * 
     * @param array $localData Dados mapeados
     * @return array|false Curso existente ou false
     */
    private function findExistingCourse($localData)
    {
        // Buscar por cod_externo (ID remoto)
        if (!empty($localData['cod_externo'])) {
            $stmt = $this->localDb->prepare("SELECT id FROM courses WHERE cod_externo = :cod_externo LIMIT 1");
            $stmt->execute([':cod_externo' => $localData['cod_externo']]);
            $result = $stmt->fetch();
            if ($result) {
                return $result;
            }
        }

        // Buscar por slug
        if (!empty($localData['slug'])) {
            $stmt = $this->localDb->prepare("SELECT id FROM courses WHERE slug = :slug LIMIT 1");
            $stmt->execute([':slug' => $localData['slug']]);
            $result = $stmt->fetch();
            if ($result) {
                return $result;
            }
        }

        // Buscar por nome exato
        if (!empty($localData['nome'])) {
            $stmt = $this->localDb->prepare("SELECT id FROM courses WHERE nome = :nome LIMIT 1");
            $stmt->execute([':nome' => $localData['nome']]);
            $result = $stmt->fetch();
            if ($result) {
                return $result;
            }
        }

        return false;
    }

    /**
     * Criar novo curso no banco local
     * 
     * @param array $localData Dados mapeados
     * @return array [action => 'created', curso_id => int]
     */
    private function createCourse($localData)
    {
        try {
            $query = RemoteSyncMapping::buildInsertQuery($localData);

            $stmt = $this->localDb->prepare($query['sql']);
            $stmt->execute($query['params']);

            $courseId = $this->localDb->lastInsertId();

            return [
                'action' => 'created',
                'curso_id' => $courseId,
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao criar curso: " . $e->getMessage());
        }
    }

    /**
     * Atualizar curso existente
     * 
     * @param int $courseId ID do curso local
     * @param array $localData Dados mapeados
     * @return array [action => 'updated'|'skipped', curso_id => int]
     */
    private function updateCourse($courseId, $localData)
    {
        try {
            $query = RemoteSyncMapping::buildUpdateQuery($localData, $courseId);

            // Se não há campos para atualizar, retorna pulado
            if (empty($query['sql'])) {
                return [
                    'action' => 'skipped',
                    'curso_id' => $courseId,
                    'reason' => 'Nenhum campo para atualizar',
                ];
            }

            $stmt = $this->localDb->prepare($query['sql']);
            $stmt->execute($query['params']);

            return [
                'action' => 'updated',
                'curso_id' => $courseId,
            ];
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar curso ID {$courseId}: " . $e->getMessage());
        }
    }

    /**
     * Get synchronization log
     * 
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Get last sync timestamp from local cache
     * 
     * @return string|null
     */
    public function getLastSyncTime()
    {
        $file = __DIR__ . '/../logs/last_sync.txt';
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return null;
    }

    /**
     * Save last sync timestamp
     * 
     * @param string $timestamp Timestamp to save
     * @return bool
     */
    public function saveLastSyncTime($timestamp = null)
    {
        $timestamp = $timestamp ?? date('Y-m-d H:i:s');
        $dir = __DIR__ . '/../logs';

        // Criar diretório se não existir
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . '/last_sync.txt';
        return file_put_contents($file, $timestamp) !== false;
    }

    /**
     * Sincronizar apenas cursos modificados desde última sincronização
     * (Requer timestamp no banco remoto)
     * 
     * @param string $viewName Nome da view remota
     * @return array Resultado da sincronização
     */
    public function syncDeltaCourses($viewName = 'cursos_site')
    {
        $lastSync = $this->getLastSyncTime();

        if (!$lastSync) {
            // Se primeira sincronização, fazer completa
            $result = $this->syncAllCourses($viewName);
        } else {
            $result = $this->syncAllCourses($viewName);
            // TODO: Implementar filtragem por data se view suportar
        }

        if ($result['status'] === 'sucesso') {
            $this->saveLastSyncTime();
        }

        return $result;
    }

    /**
     * Sincronizar categorias extraídas de cursos_site
     * 
     * Extrai categorias únicas dos cursos remotos e sincroniza para course_categories
     * 
     * @param string $viewName Nome da view remota (ex: cursos_site)
     * @param int $limit Limite de registros para sincronizar
     * @return array Resultado da sincronização
     */
    public function syncCategories($viewName = 'cursos_site', $limit = 500)
    {
        try {
            // Buscar todos os cursos da view remota
            $remoteCourses = fetchAllFromView($this->remoteDb, $viewName, $limit);

            if (empty($remoteCourses)) {
                return [
                    'status' => 'aviso',
                    'mensagem' => 'Nenhum curso encontrado na view remota',
                    'log' => $this->log,
                    'stats' => ['criado' => 0, 'atualizado' => 0, 'falha' => 0],
                ];
            }

            // Extrair categorias únicas dos cursos
            $categories = [];
            foreach ($remoteCourses as $course) {
                if (!empty($course['categoria_nome']) && !isset($categories[$course['categoria_nome']])) {
                    $categories[$course['categoria_nome']] = [
                        'category_id' => $course['category_id'] ?? null,
                        'nome' => $course['categoria_nome'],
                        'slug' => $course['categoria_slug'] ?? null,
                        'descricao' => $course['categoria_descricao'] ?? null,
                        'ordem' => $course['categoria_ordem'] ?? 0,
                    ];
                }
            }

            if (empty($categories)) {
                return [
                    'status' => 'aviso',
                    'mensagem' => 'Nenhuma categoria encontrada nos cursos remotos',
                    'log' => $this->log,
                    'stats' => ['criado' => 0, 'atualizado' => 0, 'falha' => 0],
                ];
            }

            $stats = ['criado' => 0, 'atualizado' => 0, 'falha' => 0];
            $this->log[] = "Sincronizando " . count($categories) . " categoria(s) extraída(s) de cursos_site";

            foreach ($categories as $categoryData) {
                try {
                    $result = $this->syncCategory($categoryData);

                    if ($result['action'] === 'created') {
                        $stats['criado']++;
                        $this->log[] = "[Criado] Categoria: {$categoryData['nome']}";
                    } elseif ($result['action'] === 'updated') {
                        $stats['atualizado']++;
                        $this->log[] = "[Atualizado] Categoria: {$categoryData['nome']}";
                    }
                } catch (Exception $e) {
                    $stats['falha']++;
                    $this->log[] = "[ERRO] Categoria {$categoryData['nome']}: " . $e->getMessage();
                }
            }

            return [
                'status' => 'sucesso',
                'mensagem' => 'Sincronização de categorias concluída',
                'stats' => $stats,
                'log' => $this->log,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
                'log' => $this->log,
            ];
        }
    }

    /**
     * Sincronizar uma categoria individual
     * 
     * @param array $remoteCategory Dados da categoria remota/extraída
     * @return array [action => 'created'|'updated', category_id => int]
     */
    private function syncCategory($remoteCategory)
    {
        // Mapear campos básicos
        $localData = [
            'nome' => $remoteCategory['nome'] ?? null,
            'slug' => $remoteCategory['slug'] ?? $this->sanitizeSlug($remoteCategory['nome'] ?? ''),
            'descricao' => $remoteCategory['descricao'] ?? null,
            'ordem' => (int)($remoteCategory['ordem'] ?? 0),
            'ativo' => $remoteCategory['ativo'] ?? true,
        ];

        // Validar dados obrigatórios
        if (empty($localData['nome'])) {
            throw new Exception("Categoria sem nome obrigatório");
        }

        // Se slug está vazio, gerar do nome
        if (empty($localData['slug'])) {
            $localData['slug'] = $this->sanitizeSlug($localData['nome']);
        }

        // Buscar por slug ou nome
        $existingCategory = null;
        if (!empty($localData['slug'])) {
            $stmt = $this->localDb->prepare("SELECT id FROM course_categories WHERE slug = :slug LIMIT 1");
            $stmt->execute([':slug' => $localData['slug']]);
            $existingCategory = $stmt->fetch();
        }

        if (!$existingCategory) {
            $stmt = $this->localDb->prepare("SELECT id FROM course_categories WHERE nome = :nome LIMIT 1");
            $stmt->execute([':nome' => $localData['nome']]);
            $existingCategory = $stmt->fetch();
        }

        if ($existingCategory) {
            // Atualizar
            $stmt = $this->localDb->prepare("
                UPDATE course_categories 
                SET nome = :nome, slug = :slug, descricao = :descricao, ordem = :ordem, ativo = :ativo
                WHERE id = :id
            ");
            $stmt->execute([
                ':nome' => $localData['nome'],
                ':slug' => $localData['slug'],
                ':descricao' => $localData['descricao'],
                ':ordem' => $localData['ordem'],
                ':ativo' => (int)$localData['ativo'],
                ':id' => $existingCategory['id'],
            ]);

            return ['action' => 'updated', 'category_id' => $existingCategory['id']];
        } else {
            // Criar
            $stmt = $this->localDb->prepare("
                INSERT INTO course_categories (nome, slug, descricao, ordem, ativo) 
                VALUES (:nome, :slug, :descricao, :ordem, :ativo)
            ");
            $stmt->execute([
                ':nome' => $localData['nome'],
                ':slug' => $localData['slug'],
                ':descricao' => $localData['descricao'],
                ':ordem' => $localData['ordem'],
                ':ativo' => (int)$localData['ativo'],
            ]);

            return ['action' => 'created', 'category_id' => $this->localDb->lastInsertId()];
        }
    }

    /**
     * Sincronizar modalidades extraídas de cursos_site
     * 
     * Extrai modalidades únicas dos cursos remotos e sincroniza para course_modalities
     * 
     * @param string $viewName Nome da view remota (ex: cursos_site)
     * @param int $limit Limite de registros
     * @return array Resultado da sincronização
     */
    public function syncModalities($viewName = 'cursos_site', $limit = 500)
    {
        try {
            // Buscar todos os cursos da view remota
            $remoteCourses = fetchAllFromView($this->remoteDb, $viewName, $limit);

            if (empty($remoteCourses)) {
                return [
                    'status' => 'aviso',
                    'mensagem' => 'Nenhum curso encontrado na view remota',
                    'log' => $this->log,
                    'stats' => ['criado' => 0, 'atualizado' => 0, 'falha' => 0],
                ];
            }

            // Extrair modalidades únicas dos cursos
            $modalities = [];
            foreach ($remoteCourses as $course) {
                if (!empty($course['modalidade_nome']) && !isset($modalities[$course['modalidade_nome']])) {
                    $modalities[$course['modalidade_nome']] = [
                        'modality_id' => $course['modality_id'] ?? null,
                        'nome' => $course['modalidade_nome'],
                        'slug' => $course['modalidade_slug'] ?? null,
                        'descricao' => $course['modalidade_descricao'] ?? null,
                    ];
                }
            }

            if (empty($modalities)) {
                return [
                    'status' => 'aviso',
                    'mensagem' => 'Nenhuma modalidade encontrada nos cursos remotos',
                    'log' => $this->log,
                    'stats' => ['criado' => 0, 'atualizado' => 0, 'falha' => 0],
                ];
            }

            $stats = ['criado' => 0, 'atualizado' => 0, 'falha' => 0];
            $this->log[] = "Sincronizando " . count($modalities) . " modalidade(s) extraída(s) de cursos_site";

            foreach ($modalities as $modalityData) {
                try {
                    $result = $this->syncModality($modalityData);

                    if ($result['action'] === 'created') {
                        $stats['criado']++;
                        $this->log[] = "[Criado] Modalidade: {$modalityData['nome']}";
                    } elseif ($result['action'] === 'updated') {
                        $stats['atualizado']++;
                        $this->log[] = "[Atualizado] Modalidade: {$modalityData['nome']}";
                    }
                } catch (Exception $e) {
                    $stats['falha']++;
                    $this->log[] = "[ERRO] Modalidade {$modalityData['nome']}: " . $e->getMessage();
                }
            }

            return [
                'status' => 'sucesso',
                'mensagem' => 'Sincronização de modalidades concluída',
                'stats' => $stats,
                'log' => $this->log,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
                'log' => $this->log,
            ];
        }
    }

    /**
     * Sincronizar uma modalidade individual
     * 
     * @param array $remoteModality Dados da modalidade remota/extraída
     * @return array [action => 'created'|'updated', modality_id => int]
     */
    private function syncModality($remoteModality)
    {
        // Mapear campos básicos
        $localData = [
            'nome' => $remoteModality['nome'] ?? null,
            'slug' => $remoteModality['slug'] ?? $this->sanitizeSlug($remoteModality['nome'] ?? ''),
            'descricao' => $remoteModality['descricao'] ?? null,
            'ativo' => $remoteModality['ativo'] ?? true,
        ];

        // Validar dados obrigatórios
        if (empty($localData['nome'])) {
            throw new Exception("Modalidade sem nome obrigatório");
        }

        // Se slug está vazio, gerar do nome
        if (empty($localData['slug'])) {
            $localData['slug'] = $this->sanitizeSlug($localData['nome']);
        }

        // Buscar por slug ou nome
        $existingModality = null;
        if (!empty($localData['slug'])) {
            $stmt = $this->localDb->prepare("SELECT id FROM course_modalities WHERE slug = :slug LIMIT 1");
            $stmt->execute([':slug' => $localData['slug']]);
            $existingModality = $stmt->fetch();
        }

        if (!$existingModality) {
            $stmt = $this->localDb->prepare("SELECT id FROM course_modalities WHERE nome = :nome LIMIT 1");
            $stmt->execute([':nome' => $localData['nome']]);
            $existingModality = $stmt->fetch();
        }

        if ($existingModality) {
            // Atualizar
            $stmt = $this->localDb->prepare("
                UPDATE course_modalities 
                SET nome = :nome, slug = :slug, descricao = :descricao, ativo = :ativo
                WHERE id = :id
            ");
            $stmt->execute([
                ':nome' => $localData['nome'],
                ':slug' => $localData['slug'],
                ':descricao' => $localData['descricao'],
                ':ativo' => (int)$localData['ativo'],
                ':id' => $existingModality['id'],
            ]);

            return ['action' => 'updated', 'modality_id' => $existingModality['id']];
        } else {
            // Criar
            $stmt = $this->localDb->prepare("
                INSERT INTO course_modalities (nome, slug, descricao, ativo) 
                VALUES (:nome, :slug, :descricao, :ativo)
            ");
            $stmt->execute([
                ':nome' => $localData['nome'],
                ':slug' => $localData['slug'],
                ':descricao' => $localData['descricao'],
                ':ativo' => (int)$localData['ativo'],
            ]);

            return ['action' => 'created', 'modality_id' => $this->localDb->lastInsertId()];
        }
    }

    /**
     * Sincronizar currículo/disciplinas de cursos
     * 
     * Busca dados da view disciplinas_curso_site e sincroniza com course_curriculum local
     * 
     * @param string $viewName Nome da view remota (disciplinas_curso_site)
     * @param int $limit Limite de registros
     * @return array Resultado da sincronização
     */
    public function syncCurriculum($viewName = 'disciplinas_curso_site', $limit = 5000)
    {
        try {
            // Buscar dados da view remota
            $this->log[] = "Buscando disciplinas da view: {$viewName}";
            
            $sql = "SELECT * FROM `{$viewName}` WHERE disciplina_nome IS NOT NULL AND disciplina_nome != '' LIMIT {$limit}";
            $remoteData = $this->remoteDb->query($sql)->fetchAll();

            if (empty($remoteData)) {
                return [
                    'status' => 'aviso',
                    'mensagem' => 'Nenhuma disciplina encontrada na view remota',
                    'log' => $this->log,
                    'stats' => ['criado' => 0, 'atualizado' => 0, 'falha' => 0, 'removido' => 0],
                ];
            }

            $stats = [
                'criado' => 0,
                'atualizado' => 0,
                'falha' => 0,
                'removido' => 0,
            ];

            $this->log[] = "Encontradas " . count($remoteData) . " disciplina(s) para sincronizar";

            // Agrupar disciplinas por ID do curso (campo 'id' na view = 'cod_externo' no banco local)
            $disciplinasPorCurso = [];
            foreach ($remoteData as $row) {
                // O campo 'id' na view remota corresponde ao 'cod_externo' no banco local
                $cursoId = $row['id'] ?? null;
                if ($cursoId) {
                    if (!isset($disciplinasPorCurso[$cursoId])) {
                        $disciplinasPorCurso[$cursoId] = [];
                    }
                    $disciplinasPorCurso[$cursoId][] = $row;
                }
            }

            $this->log[] = "Disciplinas agrupadas em " . count($disciplinasPorCurso) . " curso(s)";

            // Sincronizar disciplinas por curso
            foreach ($disciplinasPorCurso as $codExterno => $disciplinas) {
                try {
                    $result = $this->syncCourseCurriculum($codExterno, $disciplinas);
                    $stats['criado'] += $result['criado'];
                    $stats['atualizado'] += $result['atualizado'];
                    $stats['falha'] += $result['falha'];
                    $stats['removido'] += $result['removido'];
                } catch (Exception $e) {
                    $this->log[] = "[ERRO] Curso {$codExterno}: " . $e->getMessage();
                    $stats['falha'] += count($disciplinas);
                }
            }

            return [
                'status' => 'sucesso',
                'mensagem' => "Sincronização de currículo concluída",
                'log' => $this->log,
                'stats' => $stats,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'erro',
                'mensagem' => 'Erro ao sincronizar currículo: ' . $e->getMessage(),
                'log' => $this->log,
                'stats' => ['criado' => 0, 'atualizado' => 0, 'falha' => 0, 'removido' => 0],
            ];
        }
    }

    /**
     * Sincronizar currículo de um curso específico
     * 
     * @param string $codExterno Código externo do curso
     * @param array $disciplinas Array de disciplinas do curso
     * @return array [criado, atualizado, falha, removido]
     */
    private function syncCourseCurriculum($codExterno, $disciplinas)
    {
        $stats = ['criado' => 0, 'atualizado' => 0, 'falha' => 0, 'removido' => 0];

        // Buscar curso local pelo cod_externo
        $stmt = $this->localDb->prepare("SELECT id FROM courses WHERE cod_externo = :cod_externo LIMIT 1");
        $stmt->execute([':cod_externo' => $codExterno]);
        $course = $stmt->fetch();

        if (!$course) {
            $this->log[] = "[AVISO] Curso não encontrado localmente: {$codExterno}";
            return $stats;
        }

        $courseId = $course['id'];
        $this->log[] = "[CURSO] Sincronizando disciplinas do curso ID {$courseId} (cod_externo: {$codExterno})";

        // Buscar disciplinas existentes do curso
        $stmt = $this->localDb->prepare("SELECT id, disciplina, semestre FROM course_curriculum WHERE course_id = :course_id");
        $stmt->execute([':course_id' => $courseId]);
        $existingDisciplinas = $stmt->fetchAll();

        // Criar mapa de disciplinas existentes (disciplina+semestre -> id)
        $existingMap = [];
        foreach ($existingDisciplinas as $d) {
            $key = $this->normalizeDisciplinaKey($d['disciplina'], $d['semestre']);
            $existingMap[$key] = $d['id'];
        }

        // Processar disciplinas remotas
        $processedKeys = [];
        $ordem = 0;

        foreach ($disciplinas as $remoteDisciplina) {
            try {
                // Validar dados
                $validation = CurriculumSyncMapping::validateRemoteData($remoteDisciplina);
                if (!$validation['valid']) {
                    $stats['falha']++;
                    continue;
                }

                // Converter para formato local
                $localData = CurriculumSyncMapping::convertRemoteToLocal($remoteDisciplina);
                $localData['ordem'] = $ordem++;

                // Gerar chave única
                $key = $this->normalizeDisciplinaKey($localData['disciplina'], $localData['semestre'] ?? 1);
                $processedKeys[] = $key;

                if (isset($existingMap[$key])) {
                    // Atualizar disciplina existente
                    $this->updateCurriculumItem($existingMap[$key], $localData);
                    $stats['atualizado']++;
                } else {
                    // Criar nova disciplina
                    $this->createCurriculumItem($courseId, $localData);
                    $stats['criado']++;
                }
            } catch (Exception $e) {
                $this->log[] = "[ERRO] Disciplina: " . $e->getMessage();
                $stats['falha']++;
            }
        }

        // Remover disciplinas que não existem mais na view remota
        foreach ($existingMap as $key => $id) {
            if (!in_array($key, $processedKeys)) {
                try {
                    $stmt = $this->localDb->prepare("DELETE FROM course_curriculum WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                    $stats['removido']++;
                } catch (Exception $e) {
                    $this->log[] = "[ERRO] Ao remover disciplina ID {$id}: " . $e->getMessage();
                }
            }
        }

        $this->log[] = "[✓] Curso {$codExterno}: criado={$stats['criado']}, atualizado={$stats['atualizado']}, removido={$stats['removido']}";

        return $stats;
    }

    /**
     * Normaliza chave única da disciplina para comparação
     * 
     * @param string $disciplina Nome da disciplina
     * @param int $semestre Semestre
     * @return string Chave normalizada
     */
    private function normalizeDisciplinaKey($disciplina, $semestre)
    {
        $disciplina = mb_strtolower(trim($disciplina ?? ''));
        $disciplina = preg_replace('/\s+/', ' ', $disciplina);
        return md5("{$disciplina}|{$semestre}");
    }

    /**
     * Criar novo item de currículo
     * 
     * @param int $courseId ID do curso
     * @param array $localData Dados da disciplina
     * @return int ID inserido
     */
    private function createCurriculumItem($courseId, $localData)
    {
        // Todos os campos da view agora existem na tabela local
        // Não é mais necessário remover campos

        $localData['course_id'] = $courseId;

        $fields = [];
        $placeholders = [];
        $params = [];

        foreach ($localData as $field => $value) {
            $fields[] = "`{$field}`";
            $placeholders[] = ":{$field}";
            $params[":{$field}"] = $value;
        }

        $sql = "INSERT INTO course_curriculum (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
        
        $stmt = $this->localDb->prepare($sql);
        $stmt->execute($params);

        return $this->localDb->lastInsertId();
    }

    /**
     * Atualizar item de currículo existente
     * 
     * @param int $curriculumId ID do item
     * @param array $localData Dados da disciplina
     */
    private function updateCurriculumItem($curriculumId, $localData)
    {
        // Apenas campos de sistema não devem ser atualizados
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
            return;
        }

        $sql = "UPDATE course_curriculum SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
        $params[':id'] = $curriculumId;

        $stmt = $this->localDb->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Converter string para slug válido
     * 
     * Remove acentos, converte para minúsculas, substitui espaços por hífens
     * 
     * @param string $text Texto a converter
     * @return string Slug formatado
     */
    private function sanitizeSlug($text)
    {
        if (empty($text)) {
            return '';
        }

        // Converter para minúsculas
        $slug = strtolower($text);

        // Remover acentos
        $slug = preg_replace('/[áàâãäå]/u', 'a', $slug);
        $slug = preg_replace('/[éèêë]/u', 'e', $slug);
        $slug = preg_replace('/[íìîï]/u', 'i', $slug);
        $slug = preg_replace('/[óòôõö]/u', 'o', $slug);
        $slug = preg_replace('/[úùûü]/u', 'u', $slug);
        $slug = preg_replace('/[ç]/u', 'c', $slug);

        // Substituir caracteres especiais por hífen
        $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);

        // Remover hífens no início e fim
        $slug = trim($slug, '-');

        // Remover hífens duplicados
        $slug = preg_replace('/-+/', '-', $slug);

        return $slug;
    }
}
