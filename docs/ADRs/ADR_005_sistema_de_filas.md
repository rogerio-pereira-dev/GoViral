# ADR-005: Sistema de Filas

## Status

Aprovado

## Contexto

Após a confirmação de pagamento via webhook Stripe, o processamento (chamada ao LLM, geração do relatório HTML e envio por e-mail) deve ser assíncrono para não bloquear a resposta ao webhook e permitir escalar workers independentemente.

## Decisão

Utilizar **Redis** como driver de filas do Laravel para enfileiramento e processamento de jobs de análise.

Referência: [Laravel Queues](https://laravel.com/docs/12.x/queues)

## Consequências

- **Positivas:** Redis é rápido, suportado nativamente pelo Laravel e adequado para filas; permite múltiplos workers e retries configuráveis (ex.: a cada 5 minutos, até 12 tentativas).
- **Negativas:** Dependência de Redis disponível; em falha prolongada do Redis, jobs não são consumidos.
- **Neutras:** Escalabilidade horizontal via mais instâncias de worker; monitoramento de falhas na fila recomendado.
