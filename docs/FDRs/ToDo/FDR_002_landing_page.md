# FDR-002: Landing Page

**Feature:** 2  
**Referência:** docs/03 - Branding Manual.md, docs/04 - Features.md

---

## Como funciona

- Uma página de entrada (`/`) exibe o posicionamento do produto e um CTA para o formulário.
- **Conteúdo:** tagline "Engineered for Viral Growth"; subheadline "Turn insight into viral momentum in minutes"; texto de apoio (análise de perfil TikTok, recomendações, plano de 30 dias); tom sharp, fast, smart. Textos da landing devem usar **traduções do Laravel** (localization) para suportar en, es, pt.
- **Locale no topo da página:** o seletor de idioma fica no topo da página (ex.: header). Ao escolher en, es ou pt, o locale da aplicação é definido (ex.: `App::setLocale($locale)` no backend e persistido em session ou query; frontend usa o mesmo valor). A página re-renderiza ou recarrega com os textos no idioma escolhido. O locale definido aqui é o da sessão/página e será usado no formulário e no relatório — não há campo de locale dentro do formulário.
- **Configuração de locale:** usar [Laravel Localization](https://laravel.com/docs/12.x/localization): arquivos em `lang/en`, `lang/es`, `lang/pt` (ou `lang/*.json`); helper `__('key')` ou `@lang` nas views; locale configurável via `config/app.php` e `App::setLocale()` por request. Rotas ou middleware podem definir o locale a partir da escolha do usuário no topo.
- **CTA principal:** leva ao formulário. URL alinhada ao Branding Manual: rota `/start-growth` (reflete o CTA "Start My Growth"). Texto do CTA conforme manual (ex.: "Start My Growth").
- **Visual:** segue FDR-001 (Vue + Vuetify + Branding). Sem formulário de coleta na landing; apenas apresentação + seletor de idioma no topo + CTA.

---

## Como testar

- **Happy path:** Acessar `/`; ver tagline e subheadline; seletor de idioma no topo; ao trocar idioma, textos da página mudam (Laravel translations); clicar no CTA e ir para `/start-growth` (formulário) com o locale já definido.
- **Edge cases:** 
    - Locale padrão quando usuário não escolhe (ex.: `APP_LOCALE` ou fallback en). 
    - Persistência do locale ao navegar para formulário e página de obrigado. 
    - Todas as strings da landing traduzidas em en, es, pt. 
    - CTA e rotas funcionando; sem quebra de layout.
- **Responsividade:** layout utilizável em mobile e desktop.

---

## Critérios de aceitação

- [ ] Landing exibe tagline e subheadline do manual; textos via Laravel localization (lang files ou JSON).
- [ ] Seletor de idioma (en, es, pt) no **topo** da página; ao selecionar, locale da aplicação é definido e textos atualizados.
- [ ] CTA principal visível; leva ao formulário pela rota `/start-growth`; texto do CTA conforme manual (traduzido).
- [ ] Visual alinhado ao Branding (FDR-001).
- [ ] Sem erros de console ou quebra de layout em viewport comum.

---

## Notas de deployment

- Publicar arquivos de idioma se necessário (`php artisan lang:publish`). Garantir que `lang/en`, `lang/es`, `lang/pt` (ou equivalentes) existem com as chaves usadas na landing.
