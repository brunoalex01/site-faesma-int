#!/bin/bash
# Executa a tarefa de sincronização usada pelo cron diário das 2h.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_DIR="${SCRIPT_DIR}/../logs"
LOG_FILE="${LOG_DIR}/sync.log"
LAST_FILE="${LOG_DIR}/last_sync.txt"

mkdir -p "${LOG_DIR}"

{
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] Iniciando sincronização"
  /usr/bin/curl -fsS "http://localhost/projeto5/teste.php" >/dev/null
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] Sincronização finalizada com sucesso"
} >>"${LOG_FILE}" 2>&1

date '+%Y-%m-%d %H:%M:%S' > "${LAST_FILE}"
