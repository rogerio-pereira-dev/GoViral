# ADR-001: Plataforma de Hosting

## Status

Aprovado

## Contexto

O GoViral precisa de um ambiente de hospedagem que suporte Laravel, filas (workers), Redis e PostgreSQL, com escalabilidade horizontal e baixo custo operacional. Alternativas incluem VPS tradicionais, AWS/GCP direto, PaaS genéricos e ofertas específicas para Laravel.

## Decisão

Utilizar **Laravel Cloud** como plataforma de hosting do projeto.

Referência: [Laravel Cloud Documentation](https://cloud.laravel.com/docs/intro)

## Consequências

- **Positivas:** Integração nativa com o ecossistema Laravel, suporte a filas e workers, menor esforço de DevOps, alinhamento com a stack escolhida.
- **Negativas:** Vendor lock-in ao ecossistema Laravel; custos dependem da oferta da plataforma.
- **Neutras:** Escalabilidade horizontal deve ser validada nos limites do Laravel Cloud.
