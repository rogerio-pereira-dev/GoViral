# FDR-004: Pagamento

**Feature:** 4 (visão geral; detalhes em FDR_004_1, FDR_004_2, FDR_004_3)  
**Referência:** docs/04 - Features.md, ADR-007, ADR-016, ADR-015

---

## Como funciona

- **Pagamento na mesma página do formulário:** o checkout não é uma página externa (redirect para Stripe Hosted Checkout). O campo de pagamento (Stripe Elements ou equivalente) fica **na própria página do formulário**. O usuário preenche o formulário (FDR-003) e, na mesma tela, insere dados do cartão e conclui o pagamento. Após pagamento confirmado, o usuário é **redirecionado para a página de Obrigado** informando que receberá o relatório por e-mail em até 30 minutos.
- Sub-features: 
    - **4.1** Instalar/configurar Stripe (FDR_004_1); 
    - **4.2** Realizar pagamento no formulário e redirect para Obrigado (FDR_004_2); 
    - **4.3** Webhook de confirmação de pagamento (FDR_004_3).

---

## Como testar

- Fluxo completo: preencher formulário + pagar na mesma página → redirect para `/thank-you` (ou equivalente) com mensagem “relatório por e-mail em até 30 minutos”; webhook confirma pagamento e enfileira job.
- Ver critérios e testes em FDR_004_1, FDR_004_2, FDR_004_3.

---

## Critérios de aceitação

- [ ] Pagamento disponível **na página do formulário** (sem redirect para página externa de checkout).
- [ ] Após pagamento bem-sucedido: redirect para página de Obrigado com mensagem sobre recebimento do relatório por e-mail em até 30 minutos.
- [ ] Webhook valida assinatura; atualiza registro e enfileira job (ver FDR_004_3).

---

## Notas de deployment

- Ver FDR_004_1, FDR_004_2, FDR_004_3 para env, Stripe Dashboard e webhook.
