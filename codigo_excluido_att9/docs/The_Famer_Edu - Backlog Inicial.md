# Backlog Inicial — GitHub Issues do Farmer Edu

Baseado na documentação real (Requisitos, Regras de Negócio e Modelagem — Versão Revisada) e no `schema.sql`. Pensado para ser cadastrado no GitHub Projects já no Encontro 5, com liberação gradual pelo professor (ver cronograma).

**Legenda de status:** 🟢 Não-bloqueada (pode começar assim que o squad estiver pronto) · 🔴 Bloqueada (aguarda outra issue) · 💬 Dúvida/gap a esclarecer (bom candidato a virar uma discussão em aula antes de virar tarefa)

---

## `core` — Fase 0 (instrutor + tech leads, ao vivo)

| Nº | Issue | Prioridade | Status |
|---|---|---|---|
| 1 | Setup do repositório e estrutura de pastas por módulo vertical | Must | 🟢 |
| 2 | Implementar roteador central e conexão PDO (`core`) | Must | 🟢 |
| 3 | Rodar `schema.sql` inicial e criar tabela `usuarios` (base de auth) | Must | 🟢 |
| 4 | Subir Judge0 (self-host via Docker ou RapidAPI) | Must | 🟢 |
| 5 | Testar Ollama/Llama 3.1 8B local + definir fallback de API paga | Must | 🟢 |

## `auth` — Fase 1

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 6 | Criar tabelas `instrutores` e `alunos` (herança de `usuarios`) | — | Must | 🔴 depende de #3 |
| 7 | Login exclusivo do instrutor | RF01 | Must | 🔴 depende de #6 |
| 8 | Login exclusivo do aluno | RF08 | Must | 🔴 depende de #6 |
| 9 | 💬 Definir regra de tentativas de login incorretas e expiração de sessão | gap identificado no Encontro 4 | Should | 🟢 |

## `turmas_cursos` — Fase 1

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 10 | Criar tabelas `cursos`, `turmas`, `matriculas` | — | Must | 🟢 |
| 11 | CRUD de cursos | — | Should | 🔴 depende de #10 |
| 12 | Instrutor cria turma associada a um curso | RF02 | Must | 🔴 depende de #10, #7 |
| 13 | Instrutor visualiza turmas e alunos matriculados | RF03 | Must | 🔴 depende de #12 |
| 14 | Matricular aluno em turma | pré-requisito de RN01/RN02 | Must | 🔴 depende de #10, #8 |

## `atividades` — Fase 2

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 15 | Criar tabela `atividades` com vínculo obrigatório a uma turma | RN01 | Must | 🔴 depende de #10 |
| 16 | Instrutor cadastra, edita e remove atividades | RF04 | Must | 🔴 depende de #15 |
| 17 | Aluno seleciona curso entre os matriculados | RF09 | Must | 🔴 depende de #14 |
| 18 | Aluno visualiza atividades pendentes e já realizadas | RF10 | Must | 🔴 depende de #16 |
| 19 | Definir tipos de atividade e formato de resposta aceito | RN03 | Should | 🔴 depende de #15 |

## `submissoes` — Fase 2

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 20 | Criar tabela `submissoes` (separada de atividades) | — | Must | 🔴 depende de #15 |
| 21 | Aluno realiza atividade só dentro da plataforma | RF11, RN03 | Must | 🔴 depende de #20 |
| 22 | Restringir submissão a alunos matriculados na turma da atividade | RN02 | Must | 🔴 depende de #21, #14 |
| 23 | Integrar submissão de código com Judge0 (sandbox) | RNF Segurança | Must | 🔴 depende de #4, #20 |
| 24 | Aluno refaz atividade após feedback, dentro do prazo | RF13 | Should | 🔴 depende de #21 |

## `feedback_ia` — Fase 2

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 25 | Criar tabela `feedback_ia` | — | Must | 🔴 depende de #20 |
| 26 | Gerar resposta de referência e comparar com a resposta do aluno | RF14 | Must | 🔴 depende de #25, #5 |
| 27 | Apresentar feedback textual (pontos positivos/a melhorar) | RF15 | Must | 🔴 depende de #26 |
| 28 | Instrutor configura critérios de avaliação da IA | RF07 | Should | 🔴 depende de #26 |
| 29 | Indicador de carregamento durante resposta da IA | RNF tempo de resposta | Should | 🔴 depende de #26 |

## `notas` — Fase 2

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 30 | Criar tabela `notas` | — | Must | 🔴 depende de #25 |
| 31 | Atribuir nota sugerida com base na comparação da IA | RF16 | Must | 🔴 depende de #26, #30 |
| 32 | Instrutor revisa/altera nota sugerida antes de torná-la definitiva | RF06, RN04 | Must | 🔴 depende de #31 |
| 33 | Instrutor visualiza submissões e notas | RF05 | Must | 🔴 depende de #31 |
| 34 | Garantir no máx. 1 feedback/nota vigente por submissão, com histórico | RN07 | Should | 🔴 depende de #31 |

## `gamificacao` — Fase 3

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 35 | Criar tabela `xp_logs` | — | Must | 🔴 depende de #30 |
| 36 | Motor de regras de concessão de XP conforme desempenho | RF17 | Must | 🔴 depende de #35, #32 |
| 37 | 💬 Documentar a fórmula/critério de conversão desempenho → XP | gap a esclarecer com o instrutor | Should | 🔴 depende de #36 |

## `fazenda` — Fase 3

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 38 | Criar tabelas `fazendas` e `elementos_fazenda` | — | Must | 🔴 depende de #35 |
| 39 | Aluno acessa a Fazenda a partir de botão dedicado | RF12 | Must | 🔴 depende de #38, #8 |
| 40 | Refletir XP acumulado na evolução visual (nível, construções, plantações, animais) | RF18, RN05 | Must | 🔴 depende de #36, #38 |
| 41 | Retrocesso do estado da fazenda por desempenho insuficiente | RF19 | Must | 🔴 depende de #40 |

## `notificacoes` — Fase 3

| Nº | Issue | Relacionado | Prioridade | Status |
|---|---|---|---|---|
| 42 | Criar tabela `notificacoes` | — | Should | 🔴 depende de #15 |
| 43 | 💬 Definir quais eventos disparam notificação | não há RF explícito para este módulo — gap real da documentação | Should | 🟢 |
| 44 | Implementar envio de notificação de atividade pendente / nota disponível | — | Should | 🔴 depende de #43, #16, #32 |

## Transversais — Fases 4 e 5

| Nº | Issue | Prioridade | Status |
|---|---|---|---|
| 45 | Testes de integração: `auth` + `atividades` + `submissoes` | Must | 🔴 depende dos módulos envolvidos |
| 46 | Testes de integração: `feedback_ia` + `notas` + `gamificacao` | Must | 🔴 depende dos módulos envolvidos |
| 47 | Checklist final de segurança e LGPD (RNF Privacidade) antes da entrega | Must | 🔴 Fase 4 |
| 48 | Deploy na rede interna do SENAI | Must | 🔴 Fase 5 |

---

### Observações

- As issues marcadas com 💬 são **gaps reais da documentação**, não bugs — bons exemplos para reforçar em aula o que vimos no Encontro 4 (dúvida bem registrada vale tanto quanto requisito bem classificado).
- Nº de dependência (`depende de #X`) é só uma sugestão de ordem — ajuste livremente se a divisão real dos 13 squads por módulo for diferente da que assumi aqui (1 módulo = 1 squad, exceto onde achar melhor dividir um módulo maior em 2 squads).
- Este backlog é o ponto de partida do Encontro 5 — esperado que cresça com sub-tarefas conforme os squads avançam (ex.: cada issue de "criar tabela X" provavelmente vira várias issues menores na prática).