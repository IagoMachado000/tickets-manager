# Desafio técnico Tickets Manager

**Descrição**

Este projeto foi desenvolvido como parte de um teste técnico para vaga de Desenvolvedor Web (PHP/Laravel), com foco na construção de uma API REST organizada, documentada e seguindo boas práticas de arquitetura.

---

## Requisitos

- PHP 8.2+
- Composer
- MySQL 8+
- Laravel 12

---

## Como rodar o projeto

### 1. Clonar o repositório

```bash
git clone https://github.com/IagoMachado000/tickets-manager.git

cd tickets-manager
```

### 2. Instalar dependências

```bash
composer install
```

### 3. Copiar arquivos de ambiente

```bash
cp .env.example .env
```

### 4. Configurar banco de dados

Editar o arquivo `.env`

```php
DB_CONNECTION=mysql
DB_DATABASE=tickets_manager
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Configurar localização da aplicação

Editar o arquivo `.env`

```php
APP_LOCALE=pt_BR
```

### 6. Gerar chave da aplicação

```bash
php artisan key:generate
```

### 7. Rodar migrations

```bash
php artisan migrate
```

### 8. Popular o banco de dados

```bash
# Rodar os seeders
php artisan db:seed

# Limpar o banco e rodar os seeders
# Esse comando vai apagar todos os dados e criar novos
php artisan migrate:fresh --seed
```

### 9. Criar link de storage

```bash
php artisan storage:link
```

### 10. Rodar o servidor

```bash
php artisan serve
```

API disponível em:

```text
http://127.0.0.1:8000
```

---

## Banco de Dados

O projeto utiliza **MySQL**.

Todas as estruturas de banco são criadas através de **migrations do Laravel**.

Além disso, o repositório inclui um **Dicionário de Dados** contendo a documentação completa das tabelas e relacionamentos.

Arquivo: `docs/dicionario-de-dados.md`

O dicionário descreve:

- tabelas
- campos
- tipos de dados
- chaves estrangeiras
- relacionamentos
- finalidade de cada campo

---

## Arquitetura do Projeto

A aplicação segue uma arquitetura baseada em separação de responsabilidades:

- **Controllers**: recebem a requisição HTTP
- **Form Requests**: responsáveis por validação
- **Services**: concentram regras de negócio
- **Models**: representam entidades e relacionamentos
- **Traits**: reutilização de lógica comum (ex: ApiResponseTrait)

Essa organização facilita manutenção e escalabilidade da aplicação.

---

## Collection do Insomnia

Para facilitar os testes da API, o repositório inclui uma **collection do Insomnia** com todos os endpoints utilizados durante o desenvolvimento.

Arquivo disponível em: `docs/insomnia-collection.yaml`

A collection contém:

- autenticação (register, login, logout)
- CRUD de projetos
- CRUD de tickets
- criação de mensagens com anexos
- exemplos de requisições e payloads

### Variáveis de ambiente

A collection utiliza as seguintes variáveis:

```text
base_url_v1=http://127.0.0.1:8000/api/v1

token=SEU_TOKEN_AQUI
```

> Após realizar login na API, basta substituir o valor da variável `token` pelo token retornado pela rota de autenticação.

---

## Configuração de Localização

A aplicação foi configurada para o contexto brasileiro, garantindo consistência de datas, horários e mensagens de sistema.

### Timezone

O timezone da aplicação foi definido como:

```php
// config/app.php
'timezone' => 'America/Sao_Paulo',
```

Isso garante que:

- Datas de criação (`created_at`)
- Atualizações (`updated_at`)
- Execução de jobs (Scheduler)
- Notificações

estejam alinhadas ao horário brasileiro.

> O banco de dados permanece utilizando UTC como padrão, enquanto a aplicação realiza a conversão para o timezone configurado.

### Localização (pt-BR)

Foi utilizado o pacote:

`lucascudo/laravel-pt-BR-localization`

Repositório oficial:
[https://github.com/lucascudo/laravel-pt-BR-localization](https://github.com/lucascudo/laravel-pt-BR-localization)

Esse pacote fornece:

- Traduções de validação
- Mensagens de autenticação
- Paginação
- Password reset
- Mensagens padrão do Laravel

Instalação realizada via:

```bash
composer require lucascudo/laravel-pt-BR-localization --dev
```

Após a instalação, as traduções foram publicadas e o locale da aplicação foi configurado para:

- Alteração feita no arquivo `.env` seguindo as instruções do pacote de acordo com a versão do laravel

```env
APP_LOCALE=pt_BR
```

### Justificativa Técnica

A definição do locale e timezone no início do projeto garante:

- Consistência de dados no banco
- Padronização de mensagens de erro
- Melhor experiência para usuários finais
- Base correta para notificações e agendamentos futuros (ex: fechamento automático de tickets)

---

## Autenticação

### Visão Geral

A API utiliza **Laravel Sanctum** com autenticação baseada em **Bearer Token**, operando de forma stateless.

Foi utilizado o comando oficial:

```bash
php artisan install:api
```

Os tokens são armazenados na tabela:

```
personal_access_tokens
```

### Estratégia de Token

A autenticação segue o modelo:

- Token é gerado no **register**
- Token é gerado no **login**
- O logout invalida apenas o token atual
- Múltiplas sessões simultâneas são permitidas

### Endpoints de Autenticação

#### Register

```http
POST /api/v1/register
```

Cria um novo usuário e retorna token de acesso.

##### Request

```json
{
    "name": "João",
    "email": "joao@email.com",
    "password": "12345678",
    "password_confirmation": "12345678",
    "role": "user",
    "project_id": 1
}
```

##### Response (201)

```json
{
  "success": true,
  "message": "Usuário cadastrado com sucesso.",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxx"
  }
}
```

#### Login

```http
POST /api/v1/login
```

Autentica o usuário e retorna um novo token.

##### Request

```json
{
    "email": "joao@email.com",
    "password": "12345678"
}
```

##### Response

```json
{
  "success": true,
  "message": "Login realizado com sucesso.",
  "data": {
    "user": { ... },
    "token": "2|xxxxxxxx"
  }
}
```

#### Logout

```http
POST /api/v1/logout
Authorization: Bearer {token}
```

Invalida apenas o token atual.

##### Response

```json
{
    "success": true,
    "message": "Logout realizado com sucesso.",
    "data": null
}
```

### Proteção de Rotas

Rotas protegidas utilizam:

```php
auth:sanctum
```

Exemplo:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (Request $request) => $request->user());
});
```

### Tratamento de Erros

A API possui padronização global de exceções:

- 401 → Não autenticado
- 422 → Erro de validação
- 404 → Recurso não encontrado
- 500 → Erro interno

Exemplo 401:

```json
{
    "success": false,
    "message": "Não autenticado.",
    "data": null
}
```

### Decisões Técnicas

- Utilização do Sanctum via `install:api`
- API totalmente stateless
- Middleware `auth:sanctum` para proteção
- Invalidação apenas do token atual no logout
- Múltiplas sessões simultâneas permitidas
- Padronização global de respostas e exceções
- Bloqueio de campos extras no payload (validação estrita)

---

## Padronização de Respostas da API

### Estrutura Geral das Respostas

Todas as respostas da API seguem o mesmo formato JSON, independente do endpoint ou do tipo de resposta (sucesso ou erro).

**Resposta de sucesso (`success`):**

```json
{
  "success": true,
  "message": "Mensagem opcional",
  "data": {...},          // Conteúdo da resposta
  "meta": {...}           // Informações adicionais opcionais, como paginação
}
```

**Resposta de erro (`error`):**

```json
{
  "success": false,
  "message": "Descrição do erro",
  "data": {...}           // Informações opcionais adicionais sobre o erro
}
```

- `success`: indica se a requisição foi processada com sucesso.
- `message`: mensagem descritiva para o usuário ou front-end.
- `data`: payload com os dados solicitados ou informações de erro.
- `meta` (opcional): informações adicionais, como paginação (`total`, `per_page`, `current_page`, etc).

### Tratamento de Erros Globais

As exceções e erros são tratados de forma centralizada em `bootstrap/app.php`, garantindo consistência na API.

| Código HTTP   | Situação                                         | Exemplo de resposta                                                                                                        |
| ------------- | ------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| 422           | Erro de validação (`ValidationException`)        | `{"success": false, "message": "Erro de validação.", "data": {"campo": ["Mensagem de erro"]}}`                             |
| 404           | Recurso não encontrado (`NotFoundHttpException`) | `{"success": false, "message": "Recurso não encontrado.", "data": null}`                                                   |
| 403, 401, etc | Outras exceções HTTP (`HttpExceptionInterface`)  | `{"success": false, "message": "Mensagem da exceção ou 'Erro na requisição'.", "data": null}`                              |
| 500           | Erro interno do servidor                         | `{"success": false, "message": "Erro interno do servidor.", "data": null}` (mostra mensagem detalhada se `APP_DEBUG=true`) |

> Todas as respostas de erro incluem `success: false` e `data` como `null` ou com informações adicionais do erro.

### Uso do Trait `ApiResponseTrait`

Todos os controllers da API estendem `BaseApiController` e podem usar os métodos:

- `success($data, $message = null, $status = 200, $meta = null)`
- `error($message, $status = 400, $data = null)`

**Exemplo:**

```php
public function show(Project $project)
{
    return $this->success($project, "Projeto encontrado com sucesso");
}

public function destroy(Project $project)
{
    try {
        $project->delete();
        return $this->success(null, "Projeto deletado com sucesso");
    } catch (\Exception $e) {
        return $this->error("Não foi possível deletar o projeto", 500);
    }
}
```

### Justificativa Técnica

- **Consistência:** Todas as respostas têm o mesmo formato, facilitando o consumo por front-end ou mobile.
- **Separação de responsabilidades:** Controllers apenas chamam `success()` ou `error()`, enquanto o `bootstrap/app.php` cuida do tratamento global de exceções.
- **Facilidade de extensão:** Meta dados e mensagens adicionais podem ser facilmente adicionadas sem quebrar o padrão.

---

## CRUD Projects

### List Projects

#### Endpoint

```http
GET /api/projects
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

Header:

```http
Authorization: Bearer {token}
```

#### Descrição

Lista os projetos disponíveis de acordo com o perfil do usuário autenticado.

##### Regras de acesso:

- **user**
    - Retorna apenas o projeto associado ao usuário.

- **support**
    - Retorna todos os projetos cadastrados.

##### Filtros disponíveis

| Parâmetro | Tipo   | Descrição                                |
| --------- | ------ | ---------------------------------------- |
| `q`       | string | Filtra projetos pelo nome (LIKE %texto%) |

Exemplo:

```http
GET /api/projects?q=sistema
```

#### Paginação

A listagem é paginada com 10 registros por página.

Metadados retornados:

- total
- per_page
- current_page
- last_page
- from
- to

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Projetos listados com sucesso.",
    "data": [
        {
            "id": 1,
            "name": "Sistema Interno",
            "description": "Projeto principal da empresa",
            "created_at": "2026-03-03 10:30:00"
        }
    ],
    "meta": {
        "pagination": {
            "total": 1,
            "per_page": 10,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 1
        }
    }
}
```

#### Possíveis erros

| Código | Descrição               |
| ------ | ----------------------- |
| 401    | Não autenticado         |
| 403    | Sem permissão de acesso |

### Show Project

#### Endpoint

```http
GET /api/projects/{id}
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

```http
Authorization: Bearer {token}
```

#### Descrição

Retorna os dados de um projeto específico.

##### Regras de acesso

- **user**
    - Pode visualizar apenas o projeto ao qual está vinculado.
    - Caso tente acessar outro projeto, receberá **403 - Acesso negado**.

- **support**
    - Pode visualizar qualquer projeto cadastrado no sistema.

#### Parâmetros de rota

| Parâmetro | Tipo    | Obrigatório | Descrição     |
| --------- | ------- | ----------- | ------------- |
| `id`      | integer | Sim         | ID do projeto |

#### Exemplo de requisição

```http
GET /api/projects/3
Authorization: Bearer {token}
```

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Projeto recuperado com sucesso.",
    "data": {
        "id": 3,
        "name": "Pereira S.A.",
        "description": null,
        "created_at": "2026-03-03 12:00:53"
    }
}
```

#### Possíveis respostas de erro

| Código | Descrição                                    |
| ------ | -------------------------------------------- |
| 401    | Usuário não autenticado                      |
| 403    | Usuário sem permissão para acessar o projeto |
| 404    | Projeto não encontrado                       |

### Create Project

#### Endpoint

```http
POST /api/projects
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

```http
Authorization: Bearer {token}
```

#### Descrição

Cria um novo projeto no sistema.

#### Regras de acesso

- **support**
    - Pode criar novos projetos.

- **user**
    - Não possui permissão para criar projetos.
    - Receberá **403 - Acesso negado** caso tente realizar a operação.

#### Corpo da requisição (JSON)

| Campo         | Tipo   | Obrigatório | Descrição                                    |
| ------------- | ------ | ----------- | -------------------------------------------- |
| `name`        | string | Sim         | Nome do projeto (máx. 255 caracteres, único) |
| `description` | string | Não         | Descrição do projeto                         |

#### Regras de validação

- `name`
    - obrigatório
    - string
    - máximo 255 caracteres
    - único na tabela `projects`
    - espaços extras são removidos automaticamente

- `description`
    - opcional
    - string
    - espaços extras são removidos automaticamente

#### Exemplo de requisição

```http
POST /api/projects
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
    "name": "Sistema Financeiro",
    "description": "Projeto responsável pelo módulo financeiro"
}
```

#### Exemplo de resposta (201 Created)

```json
{
    "success": true,
    "message": "Projeto criado com sucesso.",
    "data": {
        "id": 4,
        "name": "Sistema Financeiro",
        "description": "Projeto responsável pelo módulo financeiro",
        "created_at": "2026-03-03 14:25:10"
    }
}
```

#### Possíveis respostas de erro

| Código | Descrição                                 |
| ------ | ----------------------------------------- |
| 401    | Usuário não autenticado                   |
| 403    | Usuário sem permissão para criar projetos |
| 422    | Erro de validação                         |

#### Exemplo de erro de validação (422)

```json
{
    "success": false,
    "message": "Erro de validação.",
    "data": {
        "name": ["O campo nome já está sendo utilizado."]
    }
}
```

### Update Project

#### Endpoint

```http
PATCH /api/projects/{id}
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

```http
Authorization: Bearer {token}
```

#### Descrição

Atualiza os dados de um projeto existente.

A atualização é **parcial (PATCH)**, ou seja, apenas os campos enviados na requisição serão alterados.

#### Regras de acesso

- **support**
    - Pode atualizar qualquer projeto.

- **user**
    - Não possui permissão para atualizar projetos.
    - Receberá **403 - Acesso negado** caso tente realizar a operação.

#### Parâmetros de rota

| Parâmetro | Tipo    | Obrigatório | Descrição     |
| --------- | ------- | ----------- | ------------- |
| `id`      | integer | Sim         | ID do projeto |

#### Corpo da requisição (JSON)

| Campo         | Tipo   | Obrigatório | Descrição                                    |
| ------------- | ------ | ----------- | -------------------------------------------- |
| `name`        | string | Não         | Nome do projeto (máx. 255 caracteres, único) |
| `description` | string | Não         | Descrição do projeto                         |

#### Regras de validação

##### `name`

- Opcional (`sometimes`)
- Se enviado:
    - Obrigatório
    - String
    - Máx. 255 caracteres
    - Único na tabela `projects`
    - Ignora o próprio registro na verificação de unicidade
    - Espaços extras são removidos automaticamente

##### `description`

- Opcional (`sometimes`)
- Pode ser `null`
- Deve ser string quando informado
- Espaços extras são removidos automaticamente

#### Exemplo de requisição

```http
PATCH /api/projects/4
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
    "name": "Sistema Financeiro Atualizado",
    "description": "Nova descrição do projeto"
}
```

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Projeto atualizado com sucesso.",
    "data": {
        "id": 4,
        "name": "Sistema Financeiro Atualizado",
        "description": "Nova descrição do projeto",
        "created_at": "2026-03-03 14:25:10"
    }
}
```

#### Possíveis respostas de erro

| Código | Descrição                                     |
| ------ | --------------------------------------------- |
| 401    | Usuário não autenticado                       |
| 403    | Usuário sem permissão para atualizar projetos |
| 404    | Projeto não encontrado                        |
| 422    | Erro de validação                             |

#### Exemplo de erro de validação (422)

```json
{
    "success": false,
    "message": "Erro de validação.",
    "data": {
        "name": ["O campo nome já está sendo utilizado."]
    }
}
```

#### Implementação Técnica

- Regra de unicidade com `Rule::unique()->ignore()` para evitar conflito no próprio registro.

### Delete Project

#### Endpoint

```http
DELETE /api/projects/{id}
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

```http
Authorization: Bearer {token}
```

#### Descrição

Exclui um projeto existente.
A deleção segue a estratégia definida no sistema:

- Soft delete para projetos e tickets relacionados.
- Caso seja uma deleção forçada (`forceDelete`), tickets associados também serão removidos permanentemente.

#### Regras de acesso

- **support**
    - Pode deletar qualquer projeto.

- **user**
    - Não possui permissão para deletar projetos.
    - Receberá **403 - Acesso negado** caso tente realizar a operação.

#### Parâmetros de rota

| Parâmetro | Tipo    | Obrigatório | Descrição     |
| --------- | ------- | ----------- | ------------- |
| `id`      | integer | Sim         | ID do projeto |

#### Exemplo de requisição

```http
DELETE /api/projects/4
Authorization: Bearer {token}
```

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Projeto deletado com sucesso.",
    "data": null
}
```

#### Possíveis respostas de erro

| Código | Descrição                                   |
| ------ | ------------------------------------------- |
| 401    | Usuário não autenticado                     |
| 403    | Usuário sem permissão para deletar projetos |
| 404    | Projeto não encontrado                      |

#### Implementação Técnica

- Eventos de deleção (`deleting`) no model `Project` cuidam da remoção de tickets associados:
    - Se `forceDelete`: tickets são removidos permanentemente.
    - Caso contrário: tickets recebem soft delete.

---

## CRUD Tickets

### List Tickets

#### Endpoint

```http
GET /api/projects/{id}/tickets
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

Header:

```http
Authorization: Bearer {token}
```

#### Descrição

Lista os tickets de um projeto específico de acordo com o perfil do usuário autenticado.

##### Regras de acesso

- **user**
    - Retorna apenas os tickets que ele criou dentro do projeto que pertence.
    - Se tentar acessar um projeto que não pertence, retorna **403 Acesso negado**.

- **support**
    - Retorna todos os tickets do projeto.

#### Paginação

A listagem é paginada com 10 registros por página.

Metadados retornados:

- total
- per_page
- current_page
- last_page
- from
- to

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Tickets listados com sucesso.",
    "data": {
        "tickets": [
            {
                "id": 1,
                "user_id": 1,
                "project_id": 1,
                "title": "Sed facere animi ab.",
                "description": "Qui assumenda at officiis ad...",
                "status": "in_progress",
                "last_internal_at": null,
                "closed_at": null,
                "created_at": "2026-03-03 12:00:53",
                "updated_at": "2026-03-03 12:00:53",
                "user": {
                    "id": 1,
                    "name": "Sr. Ronaldo Camacho Filho",
                    "email": "diana.mendes@example.org",
                    "role": "user",
                    "project_id": 1
                }
            }
        ],
        "project": {
            "id": 1,
            "name": "Marin e Bittencourt",
            "description": null,
            "created_at": "2026-03-03 12:00:53"
        }
    },
    "meta": {
        "pagination": {
            "total": 10,
            "per_page": 10,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 10
        }
    }
}
```

#### Possíveis erros

| Código | Descrição              |
| ------ | ---------------------- |
| 401    | Não autenticado        |
| 403    | Acesso negado          |
| 404    | Recurso não encontrado |

---

### Show Ticket

#### Endpoint

```http
GET /api/tickets/{id}
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

Header:

```http
Authorization: Bearer {token}
```

#### Descrição

Retorna os detalhes completos de um ticket específico, incluindo:

- Dados do ticket
- Usuário que criou o ticket
- Mensagens relacionadas ao ticket
- Usuário de cada mensagem
- Anexos das mensagens

#### Regras de acesso

- **user**
    - Pode visualizar apenas tickets que ele criou.
    - O ticket deve pertencer ao mesmo projeto do usuário.
    - Caso tente acessar um ticket fora dessas condições, retorna **403 Acesso negado**.

- **support**
    - Pode visualizar qualquer ticket.

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Ticket recuperado com sucesso.",
    "data": {
        "id": 1,
        "user_id": 1,
        "project_id": 1,
        "title": "Sed facere animi ab.",
        "description": "Qui assumenda at officiis ad nostrum exercitationem iure a.",
        "status": "in_progress",
        "last_internal_at": null,
        "closed_at": null,
        "created_at": "2026-03-03 12:00:53",
        "updated_at": "2026-03-03 12:00:53",
        "user": {
            "id": 1,
            "name": "Sr. Ronaldo Camacho Filho",
            "email": "diana.mendes@example.org",
            "role": "user",
            "project_id": 1
        },
        "messages": [
            {
                "id": 1,
                "ticket_id": 1,
                "user_id": 1,
                "message": "Primeira mensagem do ticket.",
                "created_at": "2026-03-03 12:10:00",
                "user": {
                    "id": 1,
                    "name": "Sr. Ronaldo Camacho Filho",
                    "email": "diana.mendes@example.org",
                    "role": "user",
                    "project_id": 1
                },
                "attachments": [
                    {
                        "id": 1,
                        "ticket_message_id": 1,
                        "file_name": "erro.png",
                        "file_path": "attachments/erro.png",
                        "file_size": 204800,
                        "mime_type": "image/png",
                        "created_at": "2026-03-03 12:10:30"
                    }
                ]
            }
        ]
    }
}
```

#### Possíveis erros

| Código | Descrição              |
| ------ | ---------------------- |
| 401    | Não autenticado        |
| 403    | Acesso negado          |
| 404    | Recurso não encontrado |

### Create Ticket

#### Endpoint

```http
POST /api/projects/{project}/tickets
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

Header:

```http
Authorization: Bearer {token}
```

#### Descrição

Cria um novo ticket vinculado a um projeto específico.

O projeto é definido pelo parâmetro da rota `{project}`.

O usuário autenticado será automaticamente definido como o criador do ticket.

#### Regras de acesso

- **user**
    - Pode criar ticket apenas no projeto ao qual pertence.
    - Se tentar criar ticket em outro projeto, retorna **403 Acesso negado**.

- **support**
    - Pode criar ticket em qualquer projeto.

#### Body da requisição

```json
{
    "title": "Erro ao acessar sistema",
    "description": "Não consigo realizar login após atualização."
}
```

#### Campos

| Campo       | Tipo   | Obrigatório | Descrição                              |
| ----------- | ------ | ----------- | -------------------------------------- |
| title       | string | Sim         | Título do ticket (máx. 255 caracteres) |
| description | string | Sim         | Descrição detalhada do problema        |

> `project_id` e `user_id` não devem ser enviados no body, pois:
>
> - O projeto é definido pela rota.
> - O usuário é obtido a partir do token autenticado.

#### Exemplo de resposta (201 Created)

```json
{
    "success": true,
    "message": "Ticket criado com sucesso.",
    "data": {
        "id": 15,
        "user_id": 1,
        "project_id": 1,
        "title": "Erro ao acessar sistema",
        "description": "Não consigo realizar login após atualização.",
        "status": "pending",
        "last_interaction_at": "2026-03-04 14:10:00",
        "closed_at": null,
        "created_at": "2026-03-04 14:10:00",
        "updated_at": "2026-03-04 14:10:00"
    }
}
```

> O endpoint de criação retorna apenas o recurso recém-criado.
> Para obter o ticket completo com relacionamentos, deve-se utilizar o endpoint GET /api/tickets/{id}.

#### Possíveis erros

| Código | Descrição              |
| ------ | ---------------------- |
| 401    | Não autenticado        |
| 403    | Acesso negado          |
| 404    | Projeto não encontrado |
| 422    | Erro de validação      |

#### Decisões Técnicas

- O `project_id` é obtido via rota REST aninhada.
- O `user_id` é definido automaticamente a partir do usuário autenticado.

### Update Ticket

#### Endpoint

```http
PUT /api/tickets/{ticket}
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

Header:

```http
Authorization: Bearer {token}
```

#### Descrição

Atualiza os dados de um ticket existente.

Apenas os campos enviados no body serão atualizados.

Sempre que um ticket é atualizado, o campo `last_interaction_at` é automaticamente atualizado para a data/hora atual.

Caso o status seja definido como `closed`, o campo `closed_at` será automaticamente preenchido.

#### Regras de acesso

- **user**
    - Pode atualizar apenas tickets:
        - do mesmo projeto ao qual pertence
        - criados por ele mesmo

    - Só pode atualizar tickets com:
        - `status = pending`
        - `closed_at = null`

    - **Não pode alterar o status do ticket**.

- **support**
    - Pode atualizar qualquer ticket.
    - Pode alterar o status do ticket, apenas se `closed_at = null`.

#### Body da requisição

```json
{
    "title": "Erro ao acessar sistema",
    "description": "Após atualização continuo sem acesso",
    "status": "in_progress"
}
```

#### Campos

| Campo       | Tipo   | Obrigatório | Descrição                |
| ----------- | ------ | ----------- | ------------------------ |
| title       | string | Não         | Novo título do ticket    |
| description | string | Não         | Nova descrição do ticket |

#### Status possíveis

| Status      | Descrição                     |
| ----------- | ----------------------------- |
| pending     | Ticket aguardando atendimento |
| in_progress | Ticket em atendimento         |
| answered    | Ticket respondido             |
| closed      | Ticket finalizado             |

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Ticket atualizado com sucesso.",
    "data": {
        "id": 15,
        "user_id": 1,
        "project_id": 1,
        "title": "Erro ao acessar sistema",
        "description": "Após atualização continuo sem acesso",
        "status": "pending",
        "last_interaction_at": "2026-03-04 14:30:00",
        "closed_at": null,
        "created_at": "2026-03-04 14:10:00",
        "updated_at": "2026-03-04 14:30:00"
    }
}
```

#### Possíveis erros

| Código | Descrição                                                |
| ------ | -------------------------------------------------------- |
| 401    | Não autenticado                                          |
| 403    | Usuário não possui permissão para atualizar o ticket     |
| 404    | Ticket não encontrado                                    |
| 422    | Ticket não pode ser alterado devido às regras de negócio |

### Delete Ticket

#### Endpoint

```http
DELETE /api/tickets/{ticket}
```

#### Autenticação

Requer autenticação via **Bearer Token (Laravel Sanctum)**.

Header:

```http
Authorization: Bearer {token}
```

#### Descrição

Remove um ticket existente do sistema.

A exclusão do ticket também afeta todas as **mensagens vinculadas a ele**.

- Se o ticket for removido via **soft delete**, suas mensagens também serão soft deleted.
- Caso seja executado um **force delete**, as mensagens também serão removidas permanentemente.

Essa lógica é executada automaticamente através de um **evento do model (`deleting`)**.

#### Regras de acesso

- **support**
    - Pode deletar qualquer ticket.

- **user**
    - Não possui permissão para deletar tickets.

Se um usuário com role `user` tentar deletar um ticket, será retornado **403 Acesso negado**.

#### Parâmetros da rota

| Parâmetro | Tipo    | Descrição                      |
| --------- | ------- | ------------------------------ |
| ticket    | integer | ID do ticket que será deletado |

#### Exemplo de resposta (200 OK)

```json
{
    "success": true,
    "message": "Ticket deletado com sucesso.",
    "data": null
}
```

#### Possíveis erros

| Código | Descrição             |
| ------ | --------------------- |
| 401    | Não autenticado       |
| 403    | Acesso negado         |
| 404    | Ticket não encontrado |

---

## Mensagens de Ticket

### Visão Geral

Após a criação de um **ticket**, usuários e membros do **suporte** podem trocar mensagens dentro dele, permitindo a comunicação para resolução da demanda.

Cada mensagem pode conter **até 3 anexos**.

Regras de acesso:

- Usuários com `role = user` só podem enviar mensagens em **seus próprios tickets**.
- Usuários com `role = support` podem responder **qualquer ticket**.
- Ao enviar uma nova mensagem:
    - `last_interaction_at` do ticket é atualizado.
    - O `status` do ticket muda automaticamente:
        - `pending` quando a mensagem é enviada pelo usuário.
        - `answered` quando a mensagem é enviada pelo suporte.

### Enviar mensagem em um ticket

#### Endpoint

```
POST /api/v1/tickets/{ticket}/messages
```

#### Autenticação

Requer **Bearer Token (Laravel Sanctum)**.

```
Authorization: Bearer {token}
```

#### Parâmetros

| Campo         | Tipo   | Obrigatório | Descrição             |
| ------------- | ------ | ----------- | --------------------- |
| message       | string | Sim         | Conteúdo da mensagem  |
| attachments[] | file   | Não         | Até 3 arquivos anexos |

#### Tipos de arquivos permitidos

```
jpg
jpeg
png
pdf
doc
docx
```

#### Tamanho máximo

```
2MB por arquivo
```

##### Exemplo de Request

Exemplo usando **multipart/form-data**:

```
POST /api/v1/tickets/36/messages
```

Body:

```
message: Lorem Ipsum is simply dummy text of the printing and typesetting industry.

attachments[]: arquivo1.png
attachments[]: documento.pdf
```

#### Exemplo de Response

```json
{
    "success": true,
    "message": "Mensagem enviada com sucesso.",
    "data": {
        "id": 105,
        "ticket_id": 36,
        "user_id": 5,
        "message": "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
        "created_at": "2026-03-04 21:07:05",
        "updated_at": "2026-03-04 21:07:05",
        "user": {
            "id": 5,
            "name": "Karine Lourenço Rangel",
            "email": "pablo.arruda@example.com",
            "role": "support",
            "project_id": 1
        },
        "attachments": [
            {
                "id": 75,
                "ticket_message_id": 105,
                "file_name": "9ea71b68-fd32-465b-b90a-68fa39285d1e.png",
                "file_path": "attachments/36/9ea71b68-fd32-465b-b90a-68fa39285d1e.png",
                "file_size": 83566,
                "mime_type": "image/png",
                "created_at": "2026-03-04 21:07:07",
                "updated_at": "2026-03-04 21:07:07"
            },
            {
                "id": 76,
                "ticket_message_id": 105,
                "file_name": "e3f4c89b-01da-4ad1-bda1-32af9533e558.pdf",
                "file_path": "attachments/36/e3f4c89b-01da-4ad1-bda1-32af9533e558.pdf",
                "file_size": 50408,
                "mime_type": "application/pdf",
                "created_at": "2026-03-04 21:07:07",
                "updated_at": "2026-03-04 21:07:07"
            }
        ]
    }
}
```

#### Estrutura de armazenamento dos anexos

Os arquivos enviados são armazenados no **disk público do Laravel** (`storage/app/public`) na seguinte estrutura:

```
attachments/{ticket_id}/{uuid}.{extensão}
```

Exemplo:

```
attachments/36/9ea71b68-fd32-465b-b90a-68fa39285d1e.png
```

Para acesso público aos arquivos, é necessário executar:

```bash
php artisan storage:link
```

---

## Decisões Técnicas

### Estratégia de exclusão de projetos

Foi adotada a estratégia de **Soft Delete** para projetos e tickets.

Motivos da escolha:

- Permitir recuperação de dados em caso de exclusão acidental
- Preservar histórico de tickets e mensagens
- Facilitar auditoria de ações no sistema
- Evitar perda definitiva de dados relacionados

Com isso:

- Projeto -> Ticket
    - Ao excluir um **projeto**, os **tickets associados também recebem soft delete**.
    - Caso seja executado um **forceDelete**, os tickets relacionados são removidos permanentemente.

- Ticket -> Mensagem
    - Ao excluir um **ticket**, as **mensagens associadas também recebem soft delete**.
    - Caso seja executado um **forceDelete**, as mensagens relacionadas são removidos permanentemente.

- Mensagem -> Anexos
    - Ao excluir uma **mensagem**, os **anexos associados também recebem soft delete**.
    - Caso seja executado um **forceDelete**, os anexos relacionados são removidos permanentemente.

Essa lógica é implementada através do evento `deleting` nos models:

- Project
- Ticket
- TicketMessage

---

## Funcionalidades planejadas (não implementadas)

Devido ao tempo disponível para realização do teste técnico, algumas funcionalidades planejadas não foram implementadas, mas foram consideradas na arquitetura do projeto.

### Notificações por e-mail

A ideia seria implementar notificações utilizando o sistema de **Notifications do Laravel**, enviando e-mails para o usuário em eventos como:

- criação de ticket
- resposta do suporte
- fechamento do ticket

### Fechamento automático de tickets inativos

Seria implementado um **Job agendado (Scheduler)** responsável por:

- identificar tickets com **7 dias ou mais sem interação**
- alterar o status para `closed`
- registrar a data em `closed_at`

Esse job seria executado via **Laravel Scheduler**.

### Policies

O controle de autorização atualmente está implementado nas **camadas de Service e validações de regra de negócio**.

Como melhoria arquitetural, seria possível mover essas regras para **Policies do Laravel**, centralizando as permissões de acesso às entidades:

- ProjectPolicy
- TicketPolicy
- TicketMessagePolicy

### Front-end

O escopo do teste permite a implementação opcional de um front-end para consumo da API.

Devido ao tempo disponível, foi priorizada a implementação do **backend e das regras de negócio da API**, garantindo:

- autenticação
- controle de acesso
- validações
- documentação
- organização da arquitetura

A API foi construída de forma totalmente **stateless**, permitindo fácil integração futura com:

- SPA (React / Vue)
- aplicações mobile
- ou um front-end em Blade.
