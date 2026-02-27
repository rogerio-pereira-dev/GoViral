# FDR-010: Scheduler para limpeza de dados

**Feature:** 10  
**Referência:** docs/04 - Features.md, ADR-011

---

## Como funciona

- Laravel Scheduler (cron) configurado no ambiente. Um comando ou job agendado roda em intervalo definido (ex.: diário ou a cada 6h).
- O comando/job de limpeza: 
    1. Remove registros de `analysis_requests` com `processing_status = sent` (caso ainda existam por atraso na deleção no Job — FDR-005). 
    2. Remove registros com `created_at` older than 24 horas, qualquer status. 
    3. Remove registros com `processing_status = failed` e `attempt_count >= 12` (caso não tenham sido deletados pelo próprio Job). Não armazenar relatórios; não criar repositório de dados (ADR-011, ADR-012). Opcional: logar quantidade de registros removidos por execução.

---

## Como testar

- **Registros sent:** Inserir registro com processing_status = sent; rodar comando; registro deve sumir.
- **Registros > 24h:** Inserir registro com created_at há 25 horas; rodar comando; registro deve sumir. Registro com created_at há 23 horas deve permanecer (ou política diferente documentada).
- **Registros failed com 12 tentativas:** Inserir com processing_status = failed, attempt_count = 12; rodar comando; registro deve sumir.
- **Edge cases:** 
    - Registros pending ou queued antigos (> 24h): devem ser removidos pela regra de 24h. 
    - Job rodando em zero registros: sem erro. 
    - Concorrência: Job (FDR-005) e scheduler podem deletar; evitar race condition (ex.: delete por id ou por critério atômico). 
    - Cron realmente disparando: em produção, garantir que o cron do Laravel está ativo (`schedule:run` a cada minuto).

---

## Critérios de aceitação

- [ ] Comando/job de limpeza implementado; critérios: sent, created_at > 24h, failed com attempt_count >= 12.
- [ ] Scheduler registra o comando (ex.: `$schedule->command('analysis:cleanup')->daily()` ou equivalente).
- [ ] Em execução manual do comando: registros que atendem aos critérios são removidos; demais permanecem.
- [ ] Documentação ou comentário sobre como ativar o cron (`* * * * * php artisan schedule:run`) em produção.
- [ ] Opcional: log com quantidade de linhas deletadas.

---

## Notas de deployment

- Produção: garantir que o cron do sistema chama `php artisan schedule:run` a cada minuto (ou conforme doc Laravel). Laravel Cloud e muitos PaaS já configuram isso. Fuso horário do app (`APP_TIMEZONE`) afeta o "24 horas" (usar UTC ou timezone consistente para created_at).
