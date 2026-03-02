# DICIONÁRIO DE DADOS

---

# 1. TABELA: projects

### Descrição

Armazena os projetos do sistema.
Cada projeto possui usuários e tickets vinculados.

### Campos

| Campo       | Tipo         | Obrigatório | Descrição           |
| ----------- | ------------ | ----------- | ------------------- |
| id          | bigint (PK)  | ✔           | Identificador único |
| name        | varchar(255) | ✔           | Nome do projeto     |
| description | text         | ✖           | Descrição detalhada |
| created_at  | timestamp    | ✔           | Data de criação     |
| updated_at  | timestamp    | ✔           | Data de atualização |
| deleted_at  | timestamp    | ✖           | Soft delete         |

### Relacionamento

- users.project_id → projects.id
- tickets.project_id → projects.id

### Índices

| Índice            | Tipo                    | Finalidade                                       |
| ----------------- | ----------------------- | ------------------------------------------------ |
| PRIMARY (id)      | PK                      | Busca direta por ID                              |
| idx_projects_name | index(deleted_at, name) | Otimiza buscas com Soft Delete + filtro por nome |

---

# 2. TABELA: users

### Descrição

Usuários autenticados via Sanctum.
Cada usuário pertence a um único projeto.

### Campos

| Campo      | Tipo                   | Obrigatório | Descrição                |
| ---------- | ---------------------- | ----------- | ------------------------ |
| id         | bigint (PK)            | ✔           | Identificador            |
| project_id | bigint (FK)            | ✔           | Projeto ao qual pertence |
| name       | varchar(255)           | ✔           | Nome do usuário          |
| email      | varchar(255)           | ✔           | Login (único)            |
| password   | varchar(255)           | ✔           | Senha criptografada      |
| role       | enum('user','support') | ✔           | Tipo de usuário          |
| created_at | timestamp              | ✔           |                          |
| updated_at | timestamp              | ✔           |                          |
| deleted_at | timestamp              | ✖           | Soft delete              |

### Relacionamento

- project_id → projects.id

### Índices

| Índice               | Tipo              | Finalidade                 |
| -------------------- | ----------------- | -------------------------- |
| PRIMARY (id)         | PK                | Busca direta               |
| unique(email)        | Unique            | Autenticação               |
| idx_users_project_id | index(project_id) | Listar usuários do projeto |
| idx_users_role       | index(role)       | Filtro por tipo            |
| idx_users_deleted_at | index(deleted_at) | Soft delete                |

---

# 3. TABELA: tickets

### Descrição

Armazena os tickets criados pelos usuários de um projeto.

### Campos

| Campo               | Tipo         | Obrigatório | Descrição                              |
| ------------------- | ------------ | ----------- | -------------------------------------- |
| id                  | bigint (PK)  | ✔           | Identificador                          |
| project_id          | bigint (FK)  | ✔           | Projeto                                |
| user_id             | bigint (FK)  | ✔           | Criador do ticket                      |
| title               | varchar(255) | ✔           | Título                                 |
| description         | text         | ✔           | Descrição inicial                      |
| status              | enum         | ✔           | pending, in_progress, answered, closed |
| last_interaction_at | timestamp    | ✔           | Controle de inatividade                |
| closed_at           | timestamp    | ✖           | Data de fechamento                     |
| created_at          | timestamp    | ✔           |                                        |
| updated_at          | timestamp    | ✔           |                                        |
| deleted_at          | timestamp    | ✖           | Soft delete                            |

### Relacionamentos

- project_id → projects.id
- user_id → users.id

### Índices Estratégicos

| Índice                       | Tipo                           | Finalidade                  |
| ---------------------------- | ------------------------------ | --------------------------- |
| PRIMARY (id)                 | PK                             | Busca direta                |
| idx_tickets_project_id       | index(project_id)              | Listar tickets por projeto  |
| idx_tickets_user_id          | index(user_id)                 | Listar tickets do usuário   |
| idx_tickets_status           | index(status)                  | Filtro por status           |
| idx_tickets_project_status   | index(project_id, status)      | Dashboard por projeto       |
| idx_tickets_status_created   | index(status, created_at DESC) | Listagem recente por status |
| idx_tickets_last_interaction | index(last_interaction_at)     | Fechamento automático       |
| idx_tickets_deleted_at       | index(deleted_at)              | Soft delete                 |

---

# 4. TABELA: ticket_messages

### Descrição

Armazena todas as interações do ticket (histórico completo).

### Campos

| Campo      | Tipo        | Obrigatório | Descrição            |
| ---------- | ----------- | ----------- | -------------------- |
| id         | bigint (PK) | ✔           |                      |
| ticket_id  | bigint (FK) | ✔           | Ticket               |
| user_id    | bigint (FK) | ✔           | Autor                |
| message    | text        | ✔           | Conteúdo da mensagem |
| created_at | timestamp   | ✔           |                      |

### Relacionamentos

- ticket_id → tickets.id
- user_id → users.id

### Índices

| Índice                             | Tipo                         | Finalidade                 |
| ---------------------------------- | ---------------------------- | -------------------------- |
| idx_ticket_messages_ticket_id      | index(ticket_id)             | Buscar mensagens do ticket |
| idx_ticket_messages_ticket_created | index(ticket_id, created_at) | Histórico ordenado         |
| idx_ticket_messages_user_id        | index(user_id)               | Auditoria                  |

---

# 5. TABELA: ticket_attachments

### Descrição

Armazena anexos das mensagens do ticket (máximo 3 por mensagem).

### Campos

| Campo             | Tipo         | Obrigatório | Descrição          |
| ----------------- | ------------ | ----------- | ------------------ |
| id                | bigint (PK)  | ✔           |                    |
| ticket_message_id | bigint (FK)  | ✔           | Mensagem vinculada |
| file_name         | varchar(255) | ✔           | Nome original      |
| file_path         | varchar(255) | ✔           | Caminho no storage |
| file_size         | bigint       | ✔           | Tamanho em bytes   |
| mime_type         | varchar(100) | ✔           | Tipo MIME          |
| created_at        | timestamp    | ✔           |                    |

### Relacionamento

- ticket_message_id → ticket_messages.id

### Índices

| Índice                            | Tipo                     | Finalidade                |
| --------------------------------- | ------------------------ | ------------------------- |
| idx_ticket_attachments_message_id | index(ticket_message_id) | Buscar anexos da mensagem |

---

# 6. TABELA: notifications (Laravel padrão)

### Descrição

Armazena notificações enviadas ao usuário:

- Ticket criado
- Ticket movido para Em Progresso
- Ticket Respondido
- Ticket Fechado pelo usuário
- Ticket Fechado automaticamente

### Índices relevantes (padrão Laravel)

| Índice                                | Finalidade                     |
| ------------------------------------- | ------------------------------ |
| index(notifiable_id, notifiable_type) | Buscar notificações do usuário |
| index(read_at)                        | Filtro por não lidas           |

---

# Regra de Fechamento Automático

Tickets serão fechados automaticamente quando:

```sql
status != 'closed'
AND last_interaction_at <= NOW() - INTERVAL 7 DAY
```

Índice crítico:

```
idx_tickets_last_interaction
```

---

# Resultado Final da Modelagem

Estrutura simplificada:

```
projects
 ├── users
 └── tickets
       ├── ticket_messages
       │      └── ticket_attachments
```
