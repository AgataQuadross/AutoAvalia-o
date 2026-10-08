![The Farmer Edu](logo_horizontal.png)

# The Farmer Edu — Plano de Desenvolvimento
### SENAI · Aprendizagem em Assistente de Programação Web · UC Desenvolvimento de Sistemas Web/Back-End

**Premissas usadas neste plano** (ajuste se algo não bater):
- 56 alunos: 21 (manhã) + 35 (tarde), duas turmas trabalhando no mesmo projeto em paralelo
- Carga horária da UC: 202,5h — 3,75h/dia, 5 dias/semana → **54 dias letivos ≈ 11 semanas corridas** (sem contar feriados/imprevistos), tempo que precisa cobrir conteúdo E desenvolvimento
- Stack: **PHP** — assumindo PHP puro (sem framework), PDO e MySQL/MariaDB, para manter o foco nos fundamentos que a turma está aprendendo. Se vocês já usaram Laravel em UC anterior, a estrutura de pastas muda um pouco, mas as fases e a lógica de squads continuam válidas — me avisa que eu adapto.
- Git: turma já sabe `add/commit/push` básico — vamos assumir que branch e Pull Request precisam ser ensinados do zero.

---

## 1. Arquitetura técnica

### 1.1 Por que organizar por *feature*, não por camada
Com 56 pessoas sem separação front/back/BD, o maior risco técnico não é "ninguém saber back-end" — é **56 pessoas commitando nos mesmos arquivos** e gerando conflito de merge o tempo inteiro. A solução é estrutural: organizar o código em **módulos verticais** (cada um contém sua parte de BD, PHP e view), não em camadas horizontais. Cada squad "dono" de um módulo mexe quase exclusivamente na sua pasta.

```
the-farmer-edu/
├── public/
│   ├── index.php              # front controller único
│   └── assets/ (css, js, img)
├── app/
│   ├── core/                  # roteador, conexão PDO, sessão, helpers
│   │                          # (travado — só instrutor/tech leads mexem aqui)
│   └── modules/
│       ├── auth/               # login, sessão, perfis
│       ├── turmas_cursos/      # CRUD turma, curso, matrícula
│       ├── atividades/         # CRUD atividade (instrutor)
│       ├── submissoes/         # envio/execução da resposta do aluno
│       ├── feedback_ia/        # integração com IA, comparação e feedback
│       ├── notas/              # atribuição/consulta de notas
│       ├── gamificacao/        # motor de XP e regras de evolução
│       ├── fazenda/            # mini-jogo (lógica + estados)
│       └── notificacoes/       # avisos de atividade pendente/nota
│           └── cada pasta: Controller.php, Model.php, /views, routes.php
├── database/
│   ├── schema.sql              # script único de criação, versionado
│   └── seeds.sql
├── sandbox/                     # execução isolada do código do aluno
├── tests/
└── README.md                    # regras de contribuição (ver seção 4)
```

Cada módulo expõe suas próprias rotas, registradas no `core`, e só acessa dados de outro módulo através de uma função pública dele — nunca direto na tabela do outro. Isso é uma regra de arquitetura simples o bastante para ensinar e forte o bastante para evitar o caos.

### 1.2 Modelo de dados corrigido (ponto da revisão)
O diagrama atual (`Instrutor`, `Aluno`, `Atividade`, `Turma`) precisa virar algo como:

`Usuario` (base) → `Instrutor` / `Aluno` · `Curso` 1—N `Turma` · `Turma` N—N `Aluno` (matrícula) · `Atividade` N—1 `Turma` · `Submissao` N—1 `Atividade`, N—1 `Aluno` (aqui mora a nota e o histórico de tentativas — hoje a nota está errada dentro de `Atividade`, o que impede dois alunos terem notas diferentes na mesma atividade) · `FeedbackIA` 1—1 `Submissao` · `Fazenda` 1—1 `Aluno` · `Construcao`/`Plantacao`/`Animal` N—1 `Fazenda` · `XPLog` N—1 `Aluno`.

Vale desenhar isso com a turma na Fase 0 — é uma ótima aula de modelagem relacional usando o próprio projeto de vocês como estudo de caso.

### 1.3 Execução de código do aluno (segurança) — usar Judge0, não construir do zero
Boa notícia: não vale a pena o squad gastar tempo construindo sandbox próprio. **Judge0** é um sistema open-source feito exatamente para isso — expõe uma API REST simples, roda cada submissão isolada via `isolate` (namespaces + cgroups do Linux) com limite configurável de tempo de CPU, memória e processos, e suporta 60+ linguagens (PHP incluso). Duas formas de usar:
- **Self-host (Judge0 CE)**: gratuito, MIT/open-source, sobe via Docker Compose em poucos comandos. Ponto de atenção: os containers do Judge0 precisam rodar em modo `privileged` (é assim que o `isolate` acessa os recursos do kernel) — por segurança, rode isso numa VM/servidor dedicado, separado do servidor que guarda dados dos alunos, e mantenha a versão atualizada (houve CVE de sandbox escape corrigida na v1.13.1).
- **Hospedado (RapidAPI)**: tem plano básico gratuito, útil se não houver servidor disponível para self-host ou para prototipar antes de decidir.

Isso elimina praticamente todo o trabalho de segurança de execução da Fase 2 — o squad de `submissoes` só precisa: enviar o código do aluno pro Judge0, receber o resultado (stdout/stderr/veredito), e comparar com o esperado. Bem mais seguro e mais rápido do que construir isolamento próprio no tempo que vocês têm.

### 1.4 IA de correção/feedback — Llama local via Ollama é viável, com uma condição
Dá para rodar localmente e isso resolve de vez a preocupação com custo/limite de requisições — e como bônus, mantém os dados dos alunos (parte deles menores de idade) dentro do próprio servidor, o que ajuda no ponto de privacidade/LGPD já levantado. A ferramenta mais simples pra isso é o **Ollama**, que baixa e serve o modelo com poucos comandos e expõe uma API compatível com OpenAI em `localhost`.

O que decide se funciona é o hardware disponível no SENAI:
- Um modelo como **Llama 3.1 8B** (ou equivalente atual, ex. Llama 3.3 8B) quantizado roda com ~8GB de RAM/VRAM — viável até numa GPU consumer modesta (ex. RTX 3060) ou só CPU.
- **Só CPU funciona, mas devagar** (produção de poucos tokens por segundo) — na prática, o feedback da IA deixa de ser instantâneo e passa a levar de alguns segundos a mais de um minuto por submissão.
- **Concorrência é o ponto crítico**: rodando localmente em uma única máquina, as submissões dos 56 alunos tendem a ser processadas em fila, não em paralelo de verdade. Isso é aceitável se o feedback for tratado como assíncrono (aluno envia, vê "processando...", recebe o resultado depois) — o que aliás já é o comportamento recomendado independente da IA ser local ou em nuvem (RNF06 no documento revisado).

**Recomendação prática**: testar Llama 3.1 8B via Ollama já na Fase 0/1, com um lote de submissões simuladas, para medir tempo real de resposta no hardware que vocês têm disponível. Se o tempo de fila ficar inviável para uso em sala, manter uma API paga (Gemini Flash ou similar, que tende a ser a opção mais barata por chamada) como plano B só para os dias de pico — sem depender dela como única opção.

---

## 2. Fluxo de trabalho colaborativo (56 pessoas, 2 turmas)

**Sobre Jira:** o plano gratuito do Jira cobre só até 10 usuários — inviável para a turma toda sem custo, e não existe mais tier educacional gratuito para esse volume (só desconto acadêmico pago). Recomendo **GitHub Issues + GitHub Projects** (kanban integrado ao repositório, gratuito e sem limite de colaboradores) no lugar do Jira — cumpre a mesma função pedagógica e já fica junto do código e dos Pull Requests.

- **1 organização no GitHub** para a turma, **1 repositório** (monorepo, estrutura da seção 1.1).
- **Squads de 3-4 alunos**, cada um dono de um módulo ponta a ponta (≈13-14 squads para os ~12 módulos listados acima — dá pra dividir dois squads pequenos num módulo maior como `fazenda`).
- `main` protegida: só entra código via **Pull Request com revisão**. Ninguém dá push direto na `main`.
- **Tech leads rotativos**: 2-3 alunos por sprint (pode alternar manhã/tarde) revisam PRs junto com você — é a forma mais realista de ensinar code review a essa altura da turma.
- **GitHub Projects** com colunas por módulo/sprint, um card por tarefa, atribuído a um squad.
- Como as turmas não se encontram fisicamente (horários diferentes), a integração entre elas é assíncrona: PRs, board compartilhado e um canal de texto único (Discord/grupo) para squads que dependem um do outro (ex.: quem faz `auth` de manhã avisa quem faz `atividades` à tarde quando a rota de sessão está pronta).
- README do repo com: como rodar localmente, convenção de branch (`feature/nome-modulo-tarefa`), padrão de commit, checklist mínimo do PR (roda sem erro, sem `var_dump`/debug esquecido, etc.).

---

## 3. Cronograma (54 dias letivos ≈ 11 semanas)

| Fase | Dias | Foco |
|---|---|---|
| **0 — Fundação** | 1–6 | Apresentar a documentação já revisada (sem workshop de correção em aula); ensinar Git/branch/PR; instrutor+tech leads implementam o `core` (roteador, conexão PDO, sessão); formar squads; setup de ambiente; subir Judge0 e testar Llama local |
| **1 — Dados e autenticação** | 7–14 | Modelagem final do banco a partir do diagrama revisado; PDO prepared statements (link direto com segurança); squad `auth` entrega login dos dois perfis; squads paralelos já sobem CRUD básico de `turmas_cursos` |
| **2 — Núcleo pedagógico** | 15–30 | CRUD de atividades e tipos de atividade; submissão integrada ao Judge0; painel do instrutor; integração com a IA (local ou híbrida, já validada na Fase 0) — maior fase, é o coração do sistema |
| **3 — Gamificação/Fazenda** | 31–42 | Regras de XP; fazenda como mini-jogo (Canvas/JS é suficiente, não precisa de engine); integração XP → estado da fazenda |
| **4 — Integração e testes** | 43–50 | Testar o fluxo ponta a ponta entre módulos feitos por squads diferentes; corrigir bugs de integração; teste de carga leve com uso simultâneo real da turma |
| **5 — Entrega** | 51–54 | Deploy (mesmo que em rede interna do SENAI); documentação final; cada squad apresenta seu módulo |

---

## 4. Por onde começar amanhã (ordem prática)

1. Apresentar a documentação já revisada (requisitos, regras de negócio e novo diagrama de classes) na primeira aula — como material pronto, não como exercício de correção coletiva.
2. Criar a organização e o repositório no GitHub, subir a estrutura de pastas vazia + README com as regras de contribuição.
3. Subir o Judge0 (self-host ou RapidAPI) e testar Llama 3.1 8B via Ollama com o hardware disponível — essas duas decisões travam boa parte da Fase 2, então valem o tempo já na Fase 0.
4. Formar os squads e montar o board no GitHub Projects com os ~12-13 módulos já quebrados em tarefas menores.
5. Implementar o `core` (roteador + conexão + sessão) — sugiro fazer isso ao vivo com a turma toda assistindo, já que vira a base que ninguém mais deveria precisar mexer.

---

## 5. Riscos a vigiar

- **Conflito de merge em massa**: só é evitável se a separação por módulo da seção 1.1 for respeitada com disciplina desde a Fase 0.
- **Fila de processamento da IA local**: mesmo sem custo por chamada, uma única máquina rodando Llama local processa submissões em fila — se o hardware for fraco, o tempo de espera pode incomodar em dias de pico (ex. véspera de prazo). Vale ter a API paga como plano B só para esses momentos.
- **Escopo da fazenda crescer demais**: é fácil o squad de gamificação/visual gastar o cronograma todo em polimento. Fixe um escopo mínimo (ex.: 3-4 estados visuais da fazenda) antes de aceitar ideias mais ambiciosas.
- **Dados de menores de idade**: turmas de Aprendizagem costumam incluir menores. Evite pedir dados pessoais além do necessário e trate isso como ponto de aprendizado sobre privacidade, mesmo que de forma simplificada.

---

*Documento gerado a partir de: THE_FARMER_EDU.docx, Requisitos Funcionais e Não Funcionais & Regras de Negócio, e os diagramas UML (casos de uso, classes, atividades) enviados.*
