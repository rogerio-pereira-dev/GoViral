# ADR-002: Framework Backend

## Status

Aprovado

## Contexto

O backend precisa oferecer API, integração com Stripe (webhooks), filas assíncronas, envio de e-mail e integração futura com provedor de LLM. É necessário um framework maduro, com suporte a filas, billing e e-mail, e ecossistema PHP alinhado ao restante da stack (Laravel Cloud, Cashier).

## Decisão

Utilizar **Laravel** (versão estável mais recente) como framework backend.

Referência: [Laravel Documentation](https://laravel.com/docs)

## Consequências

- **Positivas:** Suporte nativo a filas (Redis), Cashier (Stripe), Mail, validação, e ampla documentação; compatibilidade com Laravel Cloud.
- **Negativas:** Stack PHP única; upgrades major exigem planejamento.
- **Neutras:** Equipe precisa conhecer Laravel para evoluir o produto.
