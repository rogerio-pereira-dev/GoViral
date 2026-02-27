# FDR-006: Configurar fila e worker

**Feature:** 6  
**Referência:** docs/04 - Features.md, ADR-005, ADR-015

---

## Como funciona

- Laravel usa Redis como driver de fila (`QUEUE_CONNECTION=redis`). Conexão Redis configurada em `config/database.php` / `.env`.
- Job `ProcessAnalysisRequest` (FDR-005) é enfileirado pelo webhook (FDR-004). Fila única (ex.: `default`) ou dedicada (ex.: `analysis`); nome consistente entre dispatch e worker.
- Worker consome a fila: `php artisan queue:work` (ou `queue:work redis --queue=default`). Configuração de tentativas: máx. 12; backoff entre tentativas (ex.: 5 minutos). Timeout do job suficiente para LLM + e-mail (ex.: 120–300 s).
- Em produção: processo(es) de worker garantidos (Laravel Cloud, supervisor, systemd ou equivalente) para que jobs não fiquem apenas no cron.

---

## Como testar

- **Happy path:** Webhook enfileira job; worker rodando processa e job some da fila; registro atualizado/deletado conforme FDR-005.
- **Worker parado:** Job permanece na fila; ao subir worker, job é processado.
- **Retry:** Forçar falha no job (ex.: LLM indisponível); verificar que job volta à fila com backoff; attempt_count sobe; após 12 tentativas job falha e registro é removido (comportamento do FDR-005).
- **Edge cases:** 
    - Redis indisponível: dispatch do job deve falhar ou ser enfileirado em sync para não perder evento do webhook (avaliar fallback). 
    - Múltiplos workers: mesmo job não processado por dois workers (Laravel lock). 
    - Timeout: job que excede timeout é liberado e retentado; garantir que attempt_count reflete isso.

---

## Critérios de aceitação

- [ ] `QUEUE_CONNECTION=redis`; Redis acessível; job é enfileirado pelo webhook e aparece na fila (ex.: Horizon ou Redis CLI).
- [ ] `php artisan queue:work` processa o job; após sucesso, job removido da fila; registro em sent + deletado.
- [ ] Retry configurado: máx. 12 tentativas; backoff aplicado (ex.: 5 min).
- [ ] Timeout do job configurado e maior que tempo típico LLM + e-mail.
- [ ] Documentação ou script para subir worker em produção (Laravel Cloud, supervisor, etc.).

---

## Notas de deployment

- Produção: garantir que o worker está sempre rodando (restart on failure). Variáveis de ambiente (Redis URL, queue name) iguais entre app e worker. Monitoramento: falhas de job e tamanho da fila (ex.: Laravel Horizon ou logs).
