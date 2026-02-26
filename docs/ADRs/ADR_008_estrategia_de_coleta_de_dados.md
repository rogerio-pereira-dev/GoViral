# ADR-008: Estratégia de Coleta de Dados do Perfil TikTok

## Status

Aprovado

## Contexto

Para gerar a análise, o sistema precisa de dados do perfil TikTok (username, bio, nicho, links de vídeos, etc.). Alternativas incluem API oficial do TikTok, scraping ou serviços terceiros, que trazem riscos legais, de manutenção e de custo.

## Decisão

No MVP, utilizar **apenas entrada manual** pelo usuário no formulário. Não integrar API do TikTok, não realizar scraping e não utilizar serviços terceiros de coleta de dados.

Conteúdo coletado: email, TikTok username, bio atual, nicho desejado, links dos últimos 3 vídeos, notas (opcional), idioma preferido.

## Consequências

- **Positivas:** Redução de risco legal e de dependência de APIs instáveis; menor complexidade de infraestrutura e custo; MVP viável sem aprovações de API.
- **Negativas:** Dados podem estar desatualizados ou incorretos; qualidade da análise depende da honestidade e precisão do usuário.
- **Neutras:** Possível evolução pós-MVP com API ou fontes adicionais, mediante nova avaliação (ex.: novo ADR).
