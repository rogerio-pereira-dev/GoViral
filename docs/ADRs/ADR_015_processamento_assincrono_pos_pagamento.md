# ADR-015: Processamento Assíncrono Pós-Pagamento

## Status

Aprovado

## Contexto

Após o pagamento confirmado via Stripe, o sistema deve chamar o LLM, montar o relatório HTML e enviar o e-mail. Fazer isso de forma síncrona no webhook tornaria a resposta lenta, aumentaria o risco de timeout e acoplaria a confiabilidade do Stripe à disponibilidade do LLM e do SES.

## Decisão

Todo o processamento **após a confirmação de pagamento** é **assíncrono**:

1. O webhook Stripe (`checkout.session.completed`) valida a assinatura, atualiza `payment_status = paid` e **enfileira um job** (Redis).
2. O webhook responde rapidamente ao Stripe (200 OK).
3. Um **worker** Laravel consome o job: atualiza `processing_status = processing`, chama o LLM, gera o HTML, envia o e-mail via SES, atualiza `processing_status = sent` e remove o registro (conforme política de retenção).

Nenhuma chamada ao LLM ou envio de e-mail é feita dentro do request do webhook.

## Consequências

- **Positivas:** Webhook estável e dentro do SLA do Stripe; escalabilidade horizontal via mais workers; falhas do LLM/SES são tratadas por retry na fila.
- **Negativas:** Entrega não é instantânea; usuário recebe o relatório em minutos (SLA alvo: até 10 min, típico 1–3 min).
- **Neutras:** Monitoramento de fila e de falhas de job é essencial para operação.
