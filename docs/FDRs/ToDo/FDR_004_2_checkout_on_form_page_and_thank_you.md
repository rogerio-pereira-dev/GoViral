# FDR-004.2: Checkout na página do formulário e página de Obrigado

**Feature:** 4.2  
**Referência:** FDR-004, FDR-003, ADR-007, ADR-015

---

## Como funciona

- **Checkout na mesma página do formulário:** o campo de pagamento (Stripe Elements / Payment Element ou Checkout Session embutido) fica na página do formulário. O usuário preenche os dados do formulário (FDR-003) e, na mesma tela, insere os dados do cartão e confirma o pagamento. Não há redirect para página externa do Stripe (Stripe Hosted Checkout); o fluxo é “formulário + pagamento na mesma página”.
- **Ordem do fluxo:** 
    1. Usuário preenche formulário (email, username, bio, etc.; locale já é o da página). 
    2. Backend pode criar um registro em `analysis_requests` (payment_status = pending) e devolver client secret ou session id para o frontend, ou o frontend primeiro envia os dados do form e depois inicia o pagamento — conforme implementação (Stripe Payment Element + Payment Intent ou Checkout Session com `mode: payment`). 
    3. Usuário conclui o pagamento na mesma página. 
    4. Após sucesso (confirmado no frontend), **redirecionar** o usuário para a **página de Obrigado**.
- **Página de Obrigado:** rota dedicada (ex.: `/thank-you`). Mensagem clara: o usuário receberá o relatório por e-mail **em até 30 minutos**. Conteúdo traduzido (Laravel localization) conforme locale. Sem formulário nem botão de pagamento; opcional: link para voltar à home ou à landing.
- A confirmação efetiva do pagamento para o backend é via **webhook** (FDR-004.3); o redirect para a página de Obrigado ocorre assim que o frontend recebe sucesso do Stripe (evitar esperar o webhook para redirecionar).

---

## Como testar

- **Happy path:** Preencher formulário; inserir cartão de teste (4242...); concluir pagamento na mesma página; redirect para `/thank-you`; página exibe mensagem “relatório por e-mail em até 30 minutos” (ou tradução equivalente).
- **Cancelar pagamento:** usuário cancela ou falha o pagamento; permanece na página do formulário; pode tentar novamente.
- **Idioma:** página de Obrigado no locale correto (en/es/pt).
- **Edge cases:** 
    - Duplo clique em “Pagar”: evitar criar dois pagamentos ou dois registros. 
    - Rede lenta: feedback de loading durante confirmação do pagamento.

---

## Critérios de aceitação

- [ ] Campo de pagamento (Stripe) na **mesma página** do formulário; sem redirect para página externa do Stripe.
- [ ] Após pagamento bem-sucedido: redirect para página de Obrigado.
- [ ] Página de Obrigado exibe mensagem de que o relatório será enviado por e-mail em até 30 minutos; texto traduzido (Laravel lang).
- [ ] Locale da página de Obrigado consistente com o da sessão/página (en/es/pt).

---

## Notas de deployment

- Chave pública Stripe no frontend (env); em produção, usar chaves live. Página de Obrigado pode ser estática (apenas view) ou com rota nomeada para facilitar redirect.
