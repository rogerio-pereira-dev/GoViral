# ADR-007: Provedor de Pagamento

## Status

Aprovado

## Contexto

O modelo de negócio é pagamento único (cerca de US$ 20), somente USD, sem assinatura no MVP. É obrigatório validar pagamento de forma confiável (webhooks) e evitar processamento de análise sem pagamento confirmado.

## Decisão

Utilizar **Stripe** como provedor de pagamento, com **Laravel Cashier** para integração no backend e **Stripe Checkout** para o fluxo de pagamento, garantindo validação de eventos via **webhooks** (ex.: `checkout.session.completed`).

Referências:
- [Laravel Cashier](https://laravel.com/docs/12.x/billing)
- [Stripe Checkout](https://docs.stripe.com/payments/checkout)
- [Stripe Webhooks](https://docs.stripe.com/webhooks)

## Consequências

- **Positivas:** Checkout hospedado reduz PCI scope; Cashier abstrai assinaturas e one-off; webhooks permitem atualizar status e disparar o job de análise de forma segura.
- **Negativas:** Dependência do Stripe e das taxas aplicadas; configuração de webhook (URL, assinatura) necessária em cada ambiente.
- **Neutras:** Assinatura do webhook deve ser sempre validada no backend (segurança).
