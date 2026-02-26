# ADR-017: Medidas de Segurança Gerais

## Status

Aprovado

## Contexto

O sistema lida com e-mails, dados de perfil e integração com pagamento. É necessário reduzir riscos de abuso, vazamento de dados e uso indevido de APIs/recursos.

## Decisão

Adotar as seguintes medidas como **padrão** do projeto:

1. **Validação de webhooks Stripe:** sempre validar assinatura (detalhado em ADR-016).
2. **Rate limiting:** aplicar limite de requisições em endpoints públicos (ex.: formulário, criação de checkout) para mitigar abuso e DDoS.
3. **Sanitização de entrada:** validar e sanitizar todos os dados enviados pelo usuário (email, URLs, texto) para evitar injeção e XSS no relatório e em e-mails.
4. **HTTPS:** garantir que toda a comunicação com a aplicação seja via HTTPS em produção.
5. **Identidade de envio (SES):** restringir o uso do SES ao domínio/identidade configurada (ex.: report@goviral.you); não enviar de endereços arbitrários.
6. **Secrets:** utilizar **variáveis de ambiente** (ou secret manager) para chaves de API (Stripe, SES, LLM); não commitar credenciais no repositório.

Referências: HLD seção 10 (Security); [Laravel Logging](https://laravel.com/docs/12.x/logging) para auditoria quando necessário.

## Consequências

- **Positivas:** Redução de superfície de ataque e de risco de vazamento; conformidade básica com boas práticas.
- **Negativas:** Rate limiting pode afetar usuários legítimos em picos; configuração de limites e de env por ambiente é necessária.
- **Neutras:** Monitoramento de logs (webhook rejeitado, rate limit, erros de envio) deve ser considerado na operação.
