# GoViral — Lista de Features

**Versão:** 1.0  
**Data:** 2026-02-26  
**Referências:** PRD, HLD, Branding Manual, ADRs

Cada feature é descrita de forma isolada e completa; quando houver dependência, outras features são referenciadas.

---

## 1. Configurar Vue + Vuetify conforme Branding Manual

**Objetivo:** Alinhar o frontend ao manual de identidade visual e de voz (docs/03 - Branding Manual.md).

**Escopo:**
- Tema Vuetify em dark mode: background base #121212 (dark charcoal).
- Cores primárias: Pink #FE2C55, Teal #25F4EE; acentos com neon glow em CTAs e destaques.
- Tipografia: Space Grotesk (headlines), Inter (body); hierarquia clara e alto contraste.
- UI: transições suaves, glow sutil em elementos interativos, micro-interações, layout limpo.
- CTAs e microcopy conforme manual (ex.: "Start My Growth", "Generate My Growth Blueprint", "Analyzing Your Growth Potential...").
- Favicon/logo: gradiente Teal→Pink, geometria limpa, movimento viral/relâmpago quando aplicável.

**Dependências:** Nenhuma (feature de fundação do frontend).  
**Relacionada com:** Feature 2 (Landing Page), Feature 3 (Formulário).

---

## 2. Landing Page

**Objetivo:** Página de entrada que comunica valor, posicionamento e direciona para o formulário.

**Escopo:**
- Conteúdo alinhado ao Branding Manual: tagline "Engineered for Viral Growth", subheadline "Turn insight into viral momentum in minutes", tom sharp/fast/smart.
- Apresentação do produto (análise de perfil TikTok, recomendações, plano de 30 dias).
- CTA principal para iniciar o fluxo (leva ao formulário).
- Seleção de idioma (locale) antes ou no início do preenchimento: English (en), Spanish (es), Portuguese (pt); valor repassado ao formulário e ao relatório (Feature 3, Feature 8).
- Visual e componentes conforme Feature 1 (Vue + Vuetify + Branding).

**Dependências:** Feature 1 (configuração Vue + Vuetify).  
**Relacionada com:** Feature 3 (Formulário), Feature 4 (Pagamento).

---

## 3. Formulário

**Objetivo:** Coletar dados do perfil TikTok e do usuário para gerar a análise e receber o relatório.

**Escopo:**
- Campos: email, TikTok username, bio atual, nicho desejado (aspiring niche), links dos últimos 3 vídeos, notas (opcional).
- Campo ou seletor de idioma (locale): en | es | pt; deve estar preenchido (via landing ou no próprio form).
- Validação de entrada (formato email, URLs válidas, tamanhos máximos); sanitização para evitar XSS/injection (ADR-017).
- Cloudflare Turnstile (captcha) no formulário para mitigar bots (Feature 9; ADR-018); token enviado no submit e validado no backend.
- Submit envia dados para o backend; backend persiste em `analysis_requests` (payment_status = pending), cria sessão Stripe Checkout e redireciona para o pagamento (Feature 4).
- Mensagens e labels no idioma selecionado quando aplicável; microcopy conforme Branding (ex.: "Start My Growth").

**Dependências:** Feature 1, Feature 2 (locale pode vir da landing).  
**Relacionada com:** Feature 4 (Pagamento), Feature 9 (Captcha).

---

## 4. Pagamento

**Objetivo:** Cobrar pagamento único (~US$ 20) em USD via Stripe e, após confirmação, disparar o processamento da análise.

### 4.1 Instalar e configurar Stripe

- Laravel Cashier (Stripe) instalado e configurado.
- Variáveis de ambiente: chaves Stripe (publishable, secret), webhook signing secret.
- Produto/preço configurado no Stripe (pagamento único, USD); valor alvo validável (ex.: $20).
- Stripe Checkout usado como fluxo de pagamento (hosted); URL de sucesso/cancelamento configuradas.
- Webhook endpoint registrado no Stripe para o evento `checkout.session.completed`.

### 4.2 Realizar pagamento

- Após submit do formulário (Feature 3), backend cria registro em `analysis_requests` (pending), cria Stripe Checkout Session associada ao registro (metadata com id da requisição ou email) e retorna URL de redirect.
- Usuário é redirecionado ao Stripe Checkout; conclui pagamento em USD.
- Após pagamento, Stripe redireciona o usuário para URL de sucesso (página de “obrigado” ou “relatório em breve”); a confirmação efetiva é via webhook (4.3).

### 4.3 Webhook de confirmação de pagamento

- Endpoint recebe evento `checkout.session.completed`.
- Validar assinatura do webhook (ADR-016); rejeitar com 4xx se inválida.
- Identificar o registro em `analysis_requests` (via session_id ou metadata).
- Atualizar `payment_status = paid`, `processing_status = queued`.
- Enfileirar job (Feature 5) para processar a análise (inicialmente job “vazio” ou com passos reais conforme implementação do Job e da Integração LLM).
- Responder 200 ao Stripe rapidamente (processamento é assíncrono — ADR-015).

**Dependências:** Feature 3 (formulário gera o registro que será pago).  
**Relacionada com:** Feature 5 (Job), Feature 6 (Fila e worker), ADR-007, ADR-016.

---

## 5. Job de processamento da análise

**Objetivo:** Orquestrar, em background, a geração do relatório e o envio por e-mail após pagamento confirmado.

**Escopo:**
- Job único (ex.: `ProcessAnalysisRequest`) acionado pelo webhook (Feature 4.3).
- Recebe o id (UUID) da requisição de análise; carrega registro de `analysis_requests` (somente payment_status = paid).
- Atualiza `processing_status = processing`, incrementa `attempt_count`.
- Chama a integração LLM (Feature 7) para obter o conteúdo da análise (texto estruturado ou blocos conforme template).
- Monta o relatório HTML (estrutura do PRD: Executive Summary, Profile Score, Inferred Niche, Username Suggestions, Optimized Bio, Profile Optimization, Content Ideas, Viralization Tips, 30-Day Action Plan) usando o output do LLM.
- Envia e-mail com o relatório em HTML (Feature 8).
- Em sucesso: atualiza `processing_status = sent` e remove o registro (política de retenção — ADR-011).
- Em falha: grava `last_error`, agenda retry (ex.: 5 min); após 12 falhas, marca como failed e remove registro (ADR-011). Configuração de retry via fila Laravel (Feature 6).

**Dependências:** Feature 4 (webhook enfileira o job), Feature 6 (fila/worker), Feature 7 (LLM), Feature 8 (e-mail).  
**Relacionada com:** Feature 6, Feature 7, Feature 8, ADR-015, ADR-011.

---

## 6. Configurar fila e worker

**Objetivo:** Garantir processamento assíncrono e escalável dos jobs de análise (ADR-005, ADR-015).

**Escopo:**
- Driver de fila: Redis; conexão Redis configurada no Laravel.
- Job `ProcessAnalysisRequest` (ou equivalente) implementado e enfileirado pelo webhook (Feature 4.3).
- Worker(s) Laravel consumindo a fila (local ou em Laravel Cloud); configuração de timeout e de número de tentativas (máx. 12, backoff ex.: 5 min).
- Em ambiente local: `php artisan queue:work`; em produção: processo(es) de worker garantidos pelo ambiente (Laravel Cloud ou supervisor/cron).
- Monitoramento recomendado: falhas de job, tamanho da fila (operacional — HLD e ADR-017).

**Dependências:** Feature 4 (webhook), Feature 5 (definição do job).  
**Relacionada com:** Feature 4, Feature 5, ADR-005.

---

## 7. Integração com LLM

**Objetivo:** Obter do provedor de LLM o conteúdo estruturado da análise para montagem do relatório (Feature 5). Provedor e abordagem foram adiados (ADR-014); esta feature inclui pesquisa, decisão e implementação.

### 7.1 Pesquisa e decisão do provedor de LLM

- Spike técnico: avaliar provedores candidatos (OpenAI, Gemini, Anthropic) em custo por requisição, qualidade de saída e latência.
- Avaliar pacotes Laravel (adapters, SDKs) e padrão de integração: adapter/strategy no Laravel (interface `LlmClient`, seleção por env) vs. orquestração externa (ex.: n8n).
- Produzir ADR de implementação (provedor escolhido + abordagem) e atualizar ADR-014 quando a decisão for tomada.
- Definir contrato/interface no código (ex.: método que recebe payload do formulário + locale e retorna texto estruturado ou blocos) para manter arquitetura agnóstica até a decisão.

### 7.2 Integração

- Implementar cliente (adapter) para o provedor escolhido; configurar via variáveis de ambiente (API key, modelo, etc.).
- Integrar ao pipeline do Job (Feature 5): chamada ao LLM com dados de `analysis_requests` e locale; tratamento de timeout e erros (retry no nível do job conforme Feature 6).

### 7.3 Obter relatório

- Montar prompt a partir do template (docs/LLM Prompt Template.md): placeholders USERNAME, BIO, NICHE, VIDEO_1/2/3, NOTES, LANGUAGE.
- Enviar request ao LLM; parsear resposta para a estrutura esperada (seções do relatório).
- Retornar conteúdo ao Job para geração do HTML e envio por e-mail (Feature 5, Feature 8).

**Dependências:** ADR-014 (decisão adiada; 7.1 desbloqueia 7.2 e 7.3).  
**Relacionada com:** Feature 5, Feature 8, docs/LLM Prompt Template.md.

---

## 8. Envio de e-mail com relatório

**Objetivo:** Entregar o relatório em HTML ao usuário por e-mail após processamento bem-sucedido (ADR-010).

**Escopo:**
- Provedor: AWS SES; remetente configurado (ex.: report@goviral.you); domínio verificado, DKIM/SPF (HLD).
- Laravel Mail: mailable que recebe o HTML do relatório e o email do destinatário; corpo da mensagem em HTML (não anexo).
- Assunto e texto alternativo conforme branding; idioma pode refletir o locale da requisição.
- Chamada feita pelo Job (Feature 5) após obter o conteúdo do LLM (Feature 7) e montar o HTML.
- Tratamento de falha de envio: retry pelo job (Feature 6); após 12 falhas, registro é removido (ADR-011).
- Não armazenar o relatório; não enviar PDF no MVP (ADR-010).

**Dependências:** Feature 5 (Job orquestra), Feature 7 (conteúdo do relatório).  
**Relacionada com:** Feature 5, Feature 7, ADR-006, ADR-010.

---

## 9. Captcha no formulário (Cloudflare Turnstile)

**Objetivo:** Reduzir submissões de bots no formulário sem limitar usuários reais (evitar impacto no faturamento).

**Escopo:**
- Integrar Cloudflare Turnstile no formulário (Feature 3): widget no frontend, token enviado no submit.
- Backend valida o token com a API Turnstile antes de criar o registro em `analysis_requests` e a sessão Stripe Checkout; em falha de validação, rejeitar a submissão com mensagem adequada.
- Turnstile atua como controle anti-bot; não aplicar rate limiting por IP/usuário para usuários reais (decisão de produto — ADR-018).

**Dependências:** Feature 3 (formulário).  
**Relacionada com:** Feature 3, ADR-018.

---

## 10. Scheduler para limpeza de dados — Closed

**Status:** Closed. Analyses will be used as case studies; sent report content must be retained in the database. The scheduler cleanup approach is no longer desired. See ADR-020 (Data Retention — Retain for Case Studies) and FDR-011 (persist report before email). FDR-010 is in `docs/FDRs/Closed/`.

**Objetivo (original):** Aplicar a política de retenção mínima (ADR-011): remover registros antigos ou em estado final.

**Escopo (original):** Laravel Scheduler, job de cleanup (sent, > 24h, failed com 12 tentativas).

---

## 11. Persist report in database before sending email

**Objetivo:** Ensure the report content (HTML) sent by email is stored in the database for internal case studies (ADR-020).

**Escopo:**
- New migration: column(s) to store report HTML (e.g. `report_html` on `analysis_requests`, or dedicated table).
- In the Job (Feature 5): after generating report HTML, save it to the database **before** queueing/sending the email; then send the email. Order: generate HTML → persist → send.
- The `analysis_requests` record is not deleted after successful send; it remains with the stored report for internal use.

**Dependências:** Feature 5 (Job), Feature 7 (report content), Feature 8 (email).  
**Relacionada com:** ADR-020, FDR-011.

---

## Resumo de dependências entre features

| Feature | Depende de | Bloqueia / alimenta |
|--------|------------|----------------------|
| 1. Vue + Vuetify Branding | — | 2, 3 |
| 2. Landing Page | 1 | 3 (locale), 4 |
| 3. Formulário | 1, 2, 9 (captcha) | 4 |
| 4. Pagamento (4.1–4.3) | 3 | 5, 6 |
| 5. Job | 4, 6, 7, 8 | 8 (envio), 10 (limpeza lógica) |
| 6. Fila e worker | 4, 5 | 5 |
| 7. Integração LLM (7.1–7.3) | ADR-014 (decisão) | 5, 8 |
| 8. E-mail com relatório | 5, 7 | — |
| 9. Captcha Turnstile | 3 (formulário) | 3 |
| 10. Scheduler limpeza | — | — (closed) |
| 11. Persist report before email | 5, 7, 8 | — |

Documento vivo: novas features ou refinamentos devem ser adicionados aqui e, quando for o caso, refletidos em ADRs.
