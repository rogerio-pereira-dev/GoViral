# FDR-001: Configurar Vue + Vuetify conforme Branding Manual

**Feature:** 1  
**Referência:** docs/03 - Branding Manual.md, docs/04 - Features.md

---

## Como funciona

- Aplicação Vue (Inertia) usa Vuetify com tema customizado.
- **Tema:** dark mode; background base `#121212`; cores primárias Pink `#FE2C55`, Teal `#25F4EE`; acentos com efeito de neon/glow em CTAs e destaques.
- **Tipografia:** Space Grotesk para headlines, Inter para body; hierarquia clara e alto contraste.
- **UI:** transições suaves em hover, glow sutil em elementos interativos, micro-interações; layout limpo, sem poluição visual.
- **Microcopy/CTAs** usam textos do manual `docs/03 - Branding Manual.md` (ex.: "Start My Growth", "Generate My Growth Blueprint", "Analyzing Your Growth Potential...").
- **Favicon/logo:** gradiente Teal→Pink, geometria limpa; conceito viral/relâmpago quando aplicável; escalável para favicon e ícone.

---

## Como testar

- **Happy path:** Carregar landing e formulário; verificar que fundo é #121212, CTAs usam pink/teal e têm glow; headlines em Space Grotesk, body em Inter; textos de CTA batem com o manual.
- **Edge cases:** 
    - Componentes Vuetify customizados (inputs, botões): garantir que herdam cores e não quebram contraste. 
    - Favicon em múltiplos tamanhos (tab, bookmark): exibição correta.
- **Acessibilidade:** contraste de texto/fundo dentro de limites (WCAG AA quando aplicável).

---

## Critérios de aceitação

- [ ] Tema Vuetify dark com #121212, #FE2C55, #25F4EE aplicados globalmente onde definido no manual.
- [ ] Space Grotesk e Inter carregados e usados conforme manual (headlines vs body).
- [ ] CTAs e microcopy do manual presentes onde a feature 1 se aplica (ex.: botão principal "Start My Growth").
- [ ] Glow/neon sutil em CTAs e elementos interativos; transições suaves.
- [ ] Favicon/logo com gradiente Teal→Pink, legível em tamanhos pequenos.
- [ ] Nenhum estilo que viole explicitamente o manual (ex.: fundo claro na raiz).

---

## Notas de deployment

- Fontes (Space Grotesk, Inter): garantir que são carregadas (Google Fonts) em todos os ambientes.
- Variáveis de tema (cores) podem ficar em um único arquivo de tema Vuetify para facilitar manutenção.
