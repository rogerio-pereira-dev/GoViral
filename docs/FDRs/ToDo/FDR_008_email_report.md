# FDR-008: Envio de e-mail com relatório

**Feature:** 8  
**Referência:** docs/04 - Features.md, ADR-006, ADR-010

---

## Como funciona

- Provedor: AWS SES. Remetente configurado (ex.: report@goviral.you); domínio verificado, DKIM/SPF em produção.
- Laravel: mailable (ex.: `GrowthReportMail`) que recebe o HTML do relatório e o e-mail do destinatário. Corpo da mensagem em HTML (não anexo). Assunto e texto alternativo (plain) conforme branding; podem variar por locale (en/es/pt).
- O Job (FDR-005) monta o HTML com o conteúdo do LLM (FDR-007) e chama o mailable; envia para o email do registro em `analysis_requests`. Em falha de envio (SES rejeita, timeout), o job falha e entra em retry (FDR-005/006); após 12 falhas o registro é removido. Relatório não é armazenado; não há PDF no MVP (ADR-010).

---

## Como testar

- **Happy path:** Job processa; mailable enviado; e-mail recebido na caixa de entrada com HTML renderizado; assunto e remetente corretos.
- **Conteúdo:** Todas as seções do relatório presentes no HTML; links e caracteres especiais escapados; sem XSS (conteúdo do LLM sanitizado ao montar HTML).
- **Edge cases:** 
    - E-mail inválido (bounce): SES retorna erro; job falha e retenta; após 12 tentativas registro deletado. 
    - HTML muito grande: verificar limites do SES (e tamanho típico do relatório). 
    - Locale: assunto/plain em en, es ou pt conforme locale da requisição. 
    - Cliente de e-mail sem HTML: texto alternativo legível.
- **Não funcional:** Não salvar o HTML em disco ou banco; não anexar PDF.

---

## Critérios de aceitação

- [ ] Mailable configurado; envio via AWS SES; remetente e domínio configurados.
- [ ] Corpo do e-mail em HTML com todas as seções do relatório; assunto e texto alternativo definidos.
- [ ] Job chama o envio após montar o HTML; falha de envio causa retry do job; após 12 falhas registro removido.
- [ ] Conteúdo sanitizado (sem XSS); relatório não armazenado; sem PDF no MVP.
- [ ] Em produção: domínio verificado no SES; DKIM/SPF configurados.

---

## Notas de deployment

- Env: `MAIL_MAILER=ses`, credenciais AWS (ou role IAM no Laravel Cloud). Em dev: `log` ou Mailtrap para não enviar para e-mails reais. Produção: solicitar saída do sandbox SES se necessário; monitorar bounces/complaints.
