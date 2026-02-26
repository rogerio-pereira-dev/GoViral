# ADR-016: Validação de Webhooks Stripe

## Status

Aprovado

## Contexto

O Stripe envia eventos (ex.: `checkout.session.completed`) para uma URL pública. Sem validação, um atacante poderia enviar requisições falsas e disparar processamento de análise sem pagamento real ou manipular status.

## Decisão

**Sempre validar a assinatura** dos webhooks Stripe antes de processar qualquer evento. Utilizar o signing secret do webhook (configurado no Stripe Dashboard) e a biblioteca oficial (Laravel Cashier / Stripe SDK) para verificar o header `Stripe-Signature` e o payload. Requisições com assinatura inválida ou ausente devem ser rejeitadas com resposta 4xx, sem atualizar banco nem enfileirar jobs.

Referência: [Stripe Webhooks](https://docs.stripe.com/webhooks) (signature verification).

## Consequências

- **Positivas:** Garante que apenas eventos legítimos do Stripe acionem atualização de pagamento e disparo do job; proteção contra falsificação e replay (se aplicável pelo Stripe).
- **Negativas:** Configuração do secret por ambiente (dev/staging/prod); rotação do secret exige atualização da aplicação.
- **Neutras:** Logs de rejeição ajudam a detectar tentativas de abuso ou misconfig.
