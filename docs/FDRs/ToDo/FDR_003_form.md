# FDR-003: Formulário

**Feature:** 3  
**Referência:** docs/04 - Features.md, ADR-017

---

## Como funciona

- Formulário coleta: **email**, TikTok username, bio atual, aspiring niche, link vídeo 1, link vídeo 2, link vídeo 3, notas (opcional). **Não há campo de locale no formulário:** o locale é o da página, definido no topo (Landing ou no próprio header da página do formulário — FDR-002). Ao submeter, o backend usa o locale atual da aplicação (session ou request) para persistir em `analysis_requests.locale`.
- **Campo de e-mail:** deve deixar **explícito** (label, placeholder ou texto de ajuda) que é necessário um **e-mail válido**, pois o relatório da análise será enviado por e-mail. Isso reduz risco de o usuário informar e-mail falso, pagar, não receber o relatório e ter que pagar novamente.
- **Validação:** e-mail válido (formato e existência quando possível); URLs válidas nos 3 links; tamanhos máximos por campo (regras de negócio); sanitização no backend para XSS/injection (ADR-017).
- **Checkout:** o pagamento é a Feature 4 (FDR-004) e será adicionado depois; quando implementado, ficará na mesma página do formulário. Este FDR cobre os campos do formulário, a validação e o redirect para a página de Obrigado.
- **Redirect para a página de Obrigado:** após submit válido do formulário, o usuário é **redirecionado para a página de Obrigado** informando que receberá o relatório por e-mail em até 30 minutos. Esse redirect é implementado **nesta feature (FDR-003)**. Assim é possível testar o formulário por inteiro na fase de desenvolvimento sem precisar de cartão (ou cartões de teste); o pagamento (FDR-004) é adicionado em seguida, e a página de Obrigado passará a ser alcançada apenas após o pagamento concluído na mesma página.
- **Microcopy:** labels e botão conforme Branding (ex.: "Start My Growth"); mensagens de validação no idioma do locale da página (Laravel translations).

---

## Como testar

- **Happy path:** Página com locale já definido (topo); preencher todos os campos válidos; campo de e-mail exibe texto explicando que o relatório será enviado por e-mail e que o e-mail deve ser válido; submit válido → **redirect para a página de Obrigado** (mensagem: relatório por e-mail em até 30 min). Registro criado no backend (payment_status = pending). Quando FDR-004 estiver ativo, o fluxo será formulário + pagamento na mesma página → Obrigado.
- **Validação:** 
    -  E-mail inválido → mensagem de erro, sem avançar. 
    - URL inválida em cada um dos 3 links → erro. 
    - Campos obrigatórios vazios (exceto notas) → erro. 
    - Locale: não há campo no form; usar locale da página/sessão.
- **Edge cases:** 
    - Submit duplo (double-click): evitar duplicar registro (tratado no fluxo com pagamento — FDR-004). 
    - Caracteres especiais em bio/notes: sanitização sem quebrar exibição. 
    - URLs com query params: aceitar se URL base for válida.
- **Texto do e-mail:** verificar que o usuário vê claramente que deve informar e-mail válido para receber o relatório.

---

## Critérios de aceitação

- [ ] Campos: email (com texto explícito de que deve ser válido para envio do relatório), username, bio, niche, 3 URLs, notes opcional; **sem** campo de locale (locale da página).
- [ ] Validação de formato (email, URLs) no frontend e backend; tamanhos máximos aplicados.
- [ ] Sanitização de entrada no backend (ADR-017).
- [ ] Microcopy do Branding; mensagens de erro no idioma do locale (traduções Laravel).
- [ ] Após submit válido: redirect para a página de Obrigado com mensagem “relatório por e-mail em até 30 minutos” (implementado nesta feature; permite testar o formulário sem pagamento na fase de desenvolvimento).

---

## Notas de deployment

- Nenhuma dependência extra; traduções de validação já cobertas pelo Laravel (lang) e pelo locale definido na landing/topo.
