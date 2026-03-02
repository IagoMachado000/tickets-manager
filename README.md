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

A API utiliza **Laravel Sanctum** para autenticação baseada em **Bearer Token**, garantindo que a aplicação seja stateless e adequada para consumo via ferramentas como Insomnia, Postman ou front-end (Blade ou SPA) consumindo a API via Bearer Token.

A instalação foi realizada utilizando o comando oficial do Laravel:

```bash
php artisan install:api
```

### Funcionamento da Autenticação

A autenticação ocorre através de tokens pessoais armazenados na tabela:

```text
personal_access_tokens
```

Após o login, o usuário recebe um token que deve ser enviado no header das requisições protegidas.

### Fluxo de Autenticação

#### O usuário realiza login:

```http
POST /api/v1/login
```

#### A API retorna:

```json
{
    "token": "1|xxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
}
```

#### O token deve ser enviado nas próximas requisições no header:

```http
Authorization: Bearer {token}
```

### Proteção de Rotas

Rotas protegidas utilizam o middleware:

```php
auth:sanctum
```

Exemplo:

```php
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
```

### Teste de Funcionamento

O funcionamento do Sanctum foi validado através de:

1.  Criação manual de usuário via Tinker

    ```php
    php artisan tinker

    // criando usuário
    use App\Models\User;
    use Illuminate\Support\Facades\Hash;

    User::create([
        'name' => 'Teste',
        'email' => 'teste@email.com',
        'password' => Hash::make('123456')
    ]);

    exit
    ```

2.  Geração de token utilizando:

    ```php
    php artisan tinker

    $user = User::where('email', 'teste@email.com')->first();
    $user->createToken('teste-token')->plainTextToken;
    $token;

    // ex de saída
    1|asdhjaskdhjaskdhjkasdhjkasdh // o token foi copiado
    ```

3.  Requisição autenticada via Insomnia enviando:

    ```http
    Authorization: Bearer {token}
    ```

4.  Rota protegida para teste
    ```php
    Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
        return $request->user();
    });
    ```

A resposta esperada foi o objeto do usuário autenticado, confirmando:

```json
{
    "id": 1,
    "name": "Teste",
    "email": "teste@email.com",
    "email_verified_at": null,
    "created_at": "2026-03-02T18:26:57.000000Z",
    "updated_at": "2026-03-02T18:26:57.000000Z"
}
```

- Middleware funcionando
- Token válido
- Associação correta com usuário
- Configuração correta do Sanctum

### Decisão Técnica

Foi utilizado o Sanctum via `install:api`, seguindo a configuração oficial do Laravel 12.
Não foi necessário adicionar manualmente um guard `api`, pois o middleware `auth:sanctum` já resolve a autenticação por token de forma nativa nas versões mais recentes da framework.

### Resultado

A API está preparada para:

- Autenticação stateless
- Versionamento (`/api/v1`)
- Controle de acesso por usuário
- Implementação futura de roles e permissões

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
