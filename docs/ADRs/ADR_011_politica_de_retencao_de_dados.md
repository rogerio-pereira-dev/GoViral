# ADR-011: Política de Retenção de Dados

## Status

Aprovado

## Contexto

O produto não oferece dashboard nem histórico no MVP. Manter registros indefinidamente aumentaria armazenamento, superfície de dados e obrigações de privacidade sem benefício direto ao usuário.

## Decisão

**Excluir (deletar) o registro** em `analysis_requests` quando ocorrer qualquer uma das condições:

1. **Relatório enviado com sucesso:** após envio do e-mail.
2. **Falhas de processamento esgotadas:** após 12 tentativas de processamento (retry a cada 5 minutos, ~1 hora), o registro é marcado como falha e removido.
3. **Idade máxima:** registros mais antigos que **24 horas** são removidos por job agendado (Laravel Scheduler), independentemente do status.

Referência: [Laravel Scheduling](https://laravel.com/docs/12.x/scheduling)

## Consequências

- **Positivas:** Armazenamento mínimo; menor superfície de dados sensíveis; alinhado ao posicionamento “sem histórico”.
- **Negativas:** Impossível reenviar relatório ou auditar pedidos antigos sem logs externos; suporte a incidentes depende de logs/métricas.
- **Neutras:** Jobs de limpeza devem ser executados de forma confiável (cron/scheduler).
