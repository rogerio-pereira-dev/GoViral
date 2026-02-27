# FDR-002: Landing Page

**Feature:** 2  
**Referência:** docs/03 - Branding Manual.md, docs/04 - Features.md

---

## Como funciona

- Uma página de entrada (`/`) exibe o posicionamento do produto e um CTA para o formulário.
- **Conteúdo:** tagline "Engineered for Viral Growth"; subheadline "Turn insight into viral momentum in minutes"; texto de apoio (análise de perfil TikTok, recomendações, plano de 30 dias); tom sharp, fast, smart.
- **CTA principal:** leva o usuário ao formulário (ex.: rota `/form` ou âncora). Texto do CTA conforme manual `docs/03 - Branding Manual.md` (ex.: "Start My Growth").
- **Seleção de idioma:** en, es, pt. Pode ser no topo da página; o valor (locale) é repassado ao formulário (query, session ou estado) para ser enviado no submit e usado no relatório.
- **Visual:** segue FDR-001 (Vue + Vuetify + Branding). Sem formulário de coleta na landing; apenas apresentação + CTA + locale.

---

## Como testar

- **Happy path:** Acessar `/`; ver tagline e subheadline; clicar no CTA e ir para o formulário; selecionar idioma e verificar que o formulário recebe o locale (ex.: via URL ou estado).
- **Edge cases:** 
    - Locale padrão quando usuário não escolhe (ex.: en). 
    - Persistência do locale ao voltar da página de pagamento ou de sucesso. 
    - Conteúdo em múltiplos idiomas: se a landing tiver textos traduzidos, validar en/es/pt. 
    - CTA desabilitado ou rota inexistente: não deve quebrar a página.
- **Responsividade:** layout utilizável em mobile e desktop.

---

## Critérios de aceitação

- [ ] Landing exibe tagline e subheadline do manual.
- [ ] CTA principal visível e leva ao formulário; texto do CTA conforme manual.
- [ ] Seleção de idioma (en, es, pt) disponível; locale repassado ao formulário.
- [ ] Visual alinhado ao Branding (FDR-001).
- [ ] Sem erros de console ou quebra de layout em viewport comum.

---

## Notas de deployment

- Nenhuma dependência de env específica para a landing; rotas e assets seguem deploy padrão da aplicação.
