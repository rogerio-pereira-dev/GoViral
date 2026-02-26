# ADR-006: Provedor de E-mail

## Status

Aprovado

## Contexto

O relatório de análise deve ser entregue ao usuário por e-mail em HTML, com boa taxa de entrega e conformidade (SPF, DKIM). O remetente definido no manual é report@goviral.you.

## Decisão

Utilizar **AWS SES (Simple Email Service)** como provedor de envio de e-mail.

Referências:
- [AWS SES](https://docs.aws.amazon.com/ses/)
- [Laravel Mail](https://laravel.com/docs/12.x/mail)
- [Criação de identidades (SES)](https://docs.aws.amazon.com/ses/latest/dg/creating-identities.html)

## Consequências

- **Positivas:** Custo baixo por e-mail, integração via driver Laravel, suporte a domínio verificado, DKIM e SPF; adequado para volume moderado.
- **Negativas:** Requer verificação de domínio e aprovação para produção no SES; bounces e reclamações devem ser monitorados.
- **Neutras:** Configuração de identidade (report@goviral.you) e credenciais via variáveis de ambiente.
