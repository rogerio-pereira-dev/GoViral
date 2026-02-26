# ADR-004: Banco de Dados

## Status

Aprovado

## Contexto

O sistema precisa persistir pedidos de análise (dados do formulário, IDs do Stripe, status de pagamento e processamento) com política de retenção mínima: remoção após envio do relatório, após falhas repetidas ou após 24 horas. Não há necessidade de relatórios históricos nem dashboard no MVP.

## Decisão

Utilizar **PostgreSQL** como banco de dados relacional, com uso mínimo de armazenamento e uma tabela principal para as requisições de análise.

Referência: stack definida no HLD (seção 2).

## Consequências

- **Positivas:** PostgreSQL é robusto, suportado pelo Laravel e pelo Laravel Cloud; adequado para baixo volume e limpeza programática.
- **Negativas:** Nenhuma crítica para o escopo atual.
- **Neutras:** Uso de recursos (espaço e conexões) deve permanecer baixo dado o modelo de retenção.
