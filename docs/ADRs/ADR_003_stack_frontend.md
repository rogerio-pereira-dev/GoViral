# ADR-003: Stack Frontend

## Status

Aprovado

## Contexto

O MVP exige uma landing page e um formulário de coleta de dados (email, username TikTok, bio, nicho, links de vídeos, notas), com seleção de idioma e integração com Stripe Checkout. É necessário manter consistência com o manual de branding (dark mode, Vuetify mencionado no HLD) e boa experiência de uso sem SPA pesada.

## Decisão

Utilizar **Laravel + Inertia.js + Vue.js + Vuetify** no frontend.

Referências:
- [Laravel Frontend (Inertia)](https://laravel.com/docs/12.x/frontend)
- [Vue.js](https://vuejs.org/guide/introduction.html)
- [Vuetify](https://vuetifyjs.com)

## Consequências

- **Positivas:** Uma única aplicação Laravel, renderização server-side com interatividade Vue, componentes Vuetify para UI consistente e alinhada ao design system; menos complexidade que SPA pura.
- **Negativas:** Dependência de Inertia e Vue no frontend; temas Vuetify podem exigir customização para o branding (cores, tipografia).
- **Neutras:** Build via Vite/ferramentas Laravel; equipe precisa dominar Vue e Vuetify.
