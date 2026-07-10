# 🚀 Sprint Max

Um sistema de gerenciamento de loja desenvolvido com **PHP Puro**, **MySQL**, **HTML**, **CSS** e **JavaScript**.

O projeto começou como um CRUD simples e está evoluindo para um **mini e-commerce**, onde administradores gerenciam a loja e clientes podem visualizar produtos, adicionar ao carrinho e realizar pedidos.

---

## 📸 Preview

> Em breve...

---

## ✨ Funcionalidades

### 👑 Administrador

- Dashboard com estatísticas
- Cadastro de produtos
- Edição de produtos
- Exclusão de produtos
- Controle de estoque
- Gerenciamento de usuários
- Visualização de todos os pedidos
- Alteração do status dos pedidos
- Logs de atividades

### 👤 Usuário

- Visualizar produtos
- Pesquisar produtos
- Filtrar por categorias
- Adicionar produtos ao carrinho
- Remover produtos do carrinho
- Alterar quantidade
- Finalizar compra
- Visualizar pedidos
- Favoritar produtos
- Editar perfil

---

## 🛠️ Tecnologias

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- PDO
- Bootstrap 5
- Font Awesome

---

## 📁 Estrutura

```text
Sprint-Max/
│
├── app/
│   ├── config/
│   ├── controller/
│   └── includes/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── uploads/
│
├── pages/
│
├── database/
│
└── index.php
```

---

## 🗄️ Banco de Dados

O sistema utiliza MySQL.

Principais tabelas:

- usuarios
- produtos
- pedidos
- carrinho
- favoritos
- logs

---

## 🔑 Tipos de Usuário

### Administrador

Possui acesso completo ao sistema.

Pode:

- Gerenciar produtos
- Gerenciar usuários
- Gerenciar pedidos
- Visualizar dashboard
- Visualizar logs

### Usuário

Pode apenas:

- Navegar pela loja
- Comprar produtos
- Gerenciar carrinho
- Visualizar seus pedidos
- Editar perfil

---

## 🚀 Como executar

### 1 Clone o repositório

```bash
git clone https://github.com/destypc/sprint-max.git
```

### 2 Abra o projeto

Coloque a pasta dentro do diretório do XAMPP.

Exemplo:

```text
htdocs/sprint-max
```

### 3 Crie o banco

Crie um banco MySQL.

Importe o arquivo SQL localizado em:

```text
database/database.sql
```

### 4 Configure a conexão

Edite:

```text
app/config/conexao.php
```

Configure:

```php
$host
$banco
$usuario
$senha
```

### 5 Execute

Abra:

```text
http://localhost/sprint-max
```

---

## 📌 Funcionalidades futuras

- [x] Carrinho de compras
- [x] Favoritos
- [x] Upload de imagens
- [x] Dashboard administrativo
- [x] Histórico de pedidos
- [x] Controle de estoque
- [x] Tema claro/escuro
- [x] Responsividade completa
- [x] Sistema de notificações
- [ ] Relatórios
- [ ] Dashboard do usuário

---

## 🎯 Objetivo

Este projeto foi desenvolvido com o objetivo de praticar conceitos de:

- PHP
- CRUD
- PDO
- MySQL
- Sessões
- Autenticação
- Permissões
- Organização de código
- Desenvolvimento Web

---

## 👨‍💻 Autor

Desenvolvido por **Enzo**.

GitHub:
https://github.com/destypc

---

## ⭐ Gostou do projeto?

Se este projeto foi útil ou interessante para você, deixe uma ⭐ no repositório!
