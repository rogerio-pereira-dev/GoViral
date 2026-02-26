# ADR-009: Modelo de Persistência (Tabela Única)

## Status

Aprovado

## Contexto

O sistema precisa armazenar cada pedido de análise com dados do formulário, referências ao Stripe e status de pagamento/processamento. Não há requisito de histórico, dashboard ou relatórios armazenados no MVP.

## Decisão

Utilizar **uma única tabela**, `analysis_requests`, contendo todos os campos necessários:

- Identificação: `id` (UUID)
- Dados do formulário: `email`, `tiktok_username`, `bio`, `aspiring_niche`, `video_url_1`, `video_url_2`, `video_url_3`, `notes` (opcional), `locale` (en/es/pt)
- Stripe: `stripe_checkout_session_id`, `stripe_payment_intent_id` (opcional)
- Status: `payment_status` (pending | paid | failed), `processing_status` (queued | processing | sent | failed)
- Controle de retry: `attempt_count`, `last_error` (nullable)
- Timestamps: `created_at`, `updated_at`

Sem armazenamento de relatórios gerados; sem tabelas de histórico ou auditoria no escopo atual.

## Consequências

- **Positivas:** Modelo simples, fácil de implementar e de limpar; alinhado à política de retenção mínima; migrações e backups triviais.
- **Negativas:** Evoluções que exijam múltiplas entidades (ex.: usuário, pedidos, entregas) podem exigir refatoração.
- **Neutras:** Retenção e limpeza são tratadas em ADR separada (política de retenção).
