# ADR-018: Captcha Cloudflare Turnstile sem Rate Limit para Usuários

## Status

Aprovado

## Contexto

O formulário de coleta de dados (landing → checkout) é público e alvo de bots e abuso. É necessário reduzir submissões automatizadas sem prejudicar usuários reais. Rate limiting por IP ou por identificador pode bloquear compradores legítimos (múltiplos dispositivos, redes compartilhadas, VPNs) e impactar negativamente o faturamento. Alternativas incluem: apenas captcha; captcha + rate limit agressivo; apenas rate limit.

## Decisão

1. **Adotar Cloudflare Turnstile** como mecanismo de captcha no formulário. O token gerado pelo Turnstile será enviado no submit e validado no backend junto à API Turnstile antes de criar o registro em `analysis_requests` e a sessão Stripe Checkout. Submissões sem token válido ou com token inválido são rejeitadas.

2. **Não aplicar rate limiting para usuários reais** em endpoints do formulário e do fluxo de pagamento (criação de sessão Checkout, etc.). O captcha já atua como barreira para robôs; limitar requisições por IP ou por outro identificador poderia bloquear compradores legítimos e reduzir conversão/faturamento.

3. **Manter** validação de webhook Stripe (ADR-016) e demais medidas de segurança (ADR-017), exceto rate limit em rotas voltadas ao usuário final no fluxo de compra.

## Consequências

- **Positivas:** Redução de submissões de bots com impacto mínimo na experiência do usuário; sem risco de bloquear vendas por limite de requisições; Turnstile é leve e geralmente invisível para usuários reais em modo adequado.
- **Negativas:** Abusos manuais (humanos) não são mitigados por rate limit; dependência do Cloudflare Turnstile e da disponibilidade da API de validação.
- **Neutras:** Em cenários futuros de abuso por humanos, pode-se avaliar rate limit seletivo (ex.: por email ou após N compras) sem alterar esta decisão para o fluxo geral.
