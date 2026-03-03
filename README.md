# Desafio técnico Tickets Manager

**Descrição**

Construção de uma API REST para um sistema de Gestão de Demandas (tickets)

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
APP_LOCALE=pt_BR,
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

### List

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

#### Implementação técnica

- Como é usado Route Model Binding para recuperação do projeto, o Laravel automaticamente retorna `404 Not Found` caso o ID não exista.
