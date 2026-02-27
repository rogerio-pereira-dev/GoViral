# FDR-004.3: Webhook de confirmação de pagamento

**Feature:** 4.3  
**Referência:** FDR-004, FDR-005, ADR-016, ADR-015

---

## Como funciona

- Endpoint (ex.: `POST /stripe/webhook`) recebe eventos do Stripe. **Sempre validar a assinatura** com o header `Stripe-Signature` e `STRIPE_WEBHOOK_SECRET` (ADR-016). Se inválida → 400/403, não processar e não atualizar banco.
- Para o evento **`checkout.session.completed`** (ou o evento equivalente ao fluxo de pagamento na página): localizar o registro em `analysis_requests` via `session_id` ou metadata; atualizar `payment_status = paid`, `processing_status = queued`; **despachar o job** `ProcessAnalysisRequest` com o id do registro; responder **200** rapidamente (processamento pesado fica no job — ADR-015).
- Outros eventos podem ser ignorados ou tratados conforme documentação Stripe; pelo menos `checkout.session.completed` deve enfileirar o job.

---

## Como testar

- **Happy path:** Stripe CLI envia `checkout.session.completed` com payload válido; endpoint retorna 200; registro fica `payment_status = paid`, `processing_status = queued`; job aparece na fila.
- **Assinatura inválida:** payload sem assinatura ou com secret errado → 4xx; registro não atualizado; job não enfileirado.
- **Idempotência:** mesmo evento processado duas vezes (retry Stripe) não duplica job nem quebra estado (ex.: verificar se registro já está paid antes de enfileirar de novo).
- **session_id não encontrado:** log; responder 200 para Stripe não reenviar em loop; não atualizar registro inexistente.

---

## Critérios de aceitação

- [ ] Webhook valida assinatura; requisição inválida rejeitada (4xx).
- [ ] Para `checkout.session.completed`: registro atualizado (paid, queued); job `ProcessAnalysisRequest` enfileirado; resposta 200 em < ~5s.
- [ ] Retry do Stripe (mesmo evento) tratado de forma idempotente.

---

## Notas de deployment

- Produção: URL pública HTTPS; signing secret do Stripe Dashboard em env. Stripe CLI para testes locais.
