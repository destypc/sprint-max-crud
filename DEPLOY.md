# Deploy no Railway

Este projeto é um app **PHP + MySQL** com upload de arquivos. Ele foi
configurado para rodar no [Railway](https://railway.app) via **Docker**
(`Dockerfile`), com MySQL gerenciado e volume persistente para os uploads.

> Por que não Vercel? O Vercel é serverless (filesystem efêmero, sem MySQL
> nativo, PHP só por runtime não-oficial). Os uploads sumiriam a cada
> requisição. O Railway roda um container persistente, ideal para este app.

## Passo a passo

### 1. Criar o projeto
1. Acesse https://railway.app e entre com o GitHub.
2. **New Project → Deploy from GitHub repo** e selecione este repositório.
3. O Railway detecta o `Dockerfile` e faz o build automaticamente.

### 2. Adicionar o banco MySQL
1. No projeto, **New → Database → Add MySQL**.
2. Isso cria as variáveis `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`,
   `MYSQLUSER`, `MYSQLPASSWORD`.
3. No serviço do **app**, aba **Variables**, referencie-as (Railway permite
   `${{ MySQL.MYSQLHOST }}` etc.) ou copie os valores. O `conexao.php` já lê
   essas variáveis automaticamente.

### 3. Importar o schema do banco
Rode o `banco-migration.sql` no banco criado. Ele cria **todas** as tabelas do
zero (na ordem correta de dependências) e já semeia um usuário administrador.
Opções:
- **Railway CLI:** `railway connect MySQL` e cole o conteúdo do arquivo; ou
- Um cliente MySQL (DBeaver/TablePlus) usando as credenciais do passo 2.

> **Login inicial:** o script cria um admin padrão —
> **e-mail:** `admin@sprintmax.com` · **senha:** `admin123`.
> **Troque essa senha imediatamente** após o primeiro login (tela **Perfil**).

### 4. Volume persistente para os uploads
1. No serviço do app, **Settings → Volumes → Add Volume**.
2. Mount path: `/var/www/html/uploads`
3. Sem isso, as imagens enviadas somem a cada novo deploy.

### 5. E-mail (opcional — recuperação de senha)
Em **Variables**, adicione as `MAIL_*` (veja `.env.example`). Para Gmail, use
uma [senha de app](https://myaccount.google.com/apppasswords). Se não
configurar, o app funciona normalmente, só sem o envio de e-mail.

### 6. Gerar o domínio
Em **Settings → Networking → Generate Domain**. Pronto — o app estará no ar.

## Desenvolvimento local
Nada muda: sem as variáveis de ambiente, o `conexao.php` usa os padrões locais
(`localhost`, `root`, sem senha) e o e-mail lê o `app/config/email.php`.
