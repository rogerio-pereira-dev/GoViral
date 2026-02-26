# ADR-012: Sem Dashboard e Sem Histórico no MVP

## Status

Aprovado

## Contexto

O GoViral é posicionado como ferramenta de compra por impulso, com entrega por e-mail e sem necessidade de “área logada”. Incluir dashboard ou histórico aumentaria escopo, persistência e complexidade de autenticação sem ser essencial para o MVP.

## Decisão

No **MVP**, não implementar:

- **Dashboard** (admin ou usuário)
- **Histórico de análises** ou de pedidos para o usuário
- **Retenção de relatórios** para consulta posterior (conforme ADR-010 e ADR-011)

O fluxo é: landing → formulário → pagamento → entrega por e-mail; o usuário não acessa o sistema após o pagamento para ver relatórios antigos.

## Consequências

- **Positivas:** Escopo menor, menos código, menos dados persistidos e menos superfície de segurança; foco em conversão e entrega.
- **Negativas:** Usuário não tem “segunda via” do relatório no produto; suporte e métricas dependem de logs e ferramentas externas.
- **Neutras:** Extensões pós-MVP (ex.: reanálise, upsell) podem exigir retenção mínima ou dashboard leve; será tratado em ADRs futuros.
