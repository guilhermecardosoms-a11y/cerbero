# CERBERO - Sistema Web de Gerenciamento e Administração

Sistema web desenvolvido rigorosamente em conformidade com as especificações do documento `requisitos.md`, com foco no gerenciamento centralizado de usuários, cadastros/registros e triagem de solicitações de acesso.

---

## 📌 1. Conformidade com os Requisitos (`requisitos.md`)

### 🎯 Regras de Negócio (CORE)
- [x] **Quantidade Total de Usuários Cadastrados**: Exibida em destaque no Dashboard principal e detalhada na tela de Usuários (`usuarios.php`).
- [x] **Quantidade de Registros Cadastrados**: Métrica em tempo real de ativos/cadastros gerenciados no sistema (`registros.php`).
- [x] **Status do Sistema**: Indicador visual dinâmico com sinalizador de pulso ("Sistema Operacional", versão do PHP e status do PDO MySQL).
- [x] **Solicitações de Acesso Pendentes**: Contador em tempo real, badge no menu lateral, sino de notificações e painel de aprovação/rejeição com um clique.
- [x] **Mensagem de Boas-Vindas**: Apresentação personalizada ao usuário conectado com saudação baseada na sessão ativa.
- [x] **Identificação do Nome e Nível de Acesso**: Perfil exibido no cabeçalho e na barra lateral com badges coloridos e estilizados para `Administrador`, `Gerente` e `Operador`.

### 🛡️ Regras Globais do Projeto
- [x] **Código Limpo e Organizado**: Separação modular de responsabilidades, comentários objetivos focados em regras de negócio.
- [x] **Conexão Segura via PDO (`bd.php`)**: Instância única singleton, UTF-8 mb4, tratamento de exceções e uso de consultas preparadas (*Prepared Statements*) em 100% dos fluxos para eliminar riscos de SQL Injection.
- [x] **Separação de Arquitetura**: 
  - Conexão e ambiente: `bd.php` / `config/`
  - Processamento backend isolado: `backend/`
  - Interfaces e views: `index.php`, `login.php`, `solicitar_acesso.php`, etc.
  - Componentes reutilizáveis: `includes/`
- [x] **Tailwind CSS Mobile First**: Layout responsivo adaptado para smartphones, tablets e desktops com navegação em gaveta retrátil.
- [x] **Notificações via TOAST**: Sistema nativo de toasts animados para confirmação de sucessos, alertas e erros de validação.
- [x] **Modais Personalizados**: Substituição completa dos comandos nativos `alert()` e `confirm()` por modais estilizados em Tailwind.
- [x] **Validação Segura no Backend**: Validação estrita de todos os dados recebidos via POST/GET, sem confiar exclusivamente no front-end.

---

## 💻 2. Stack Tecnológico

| Camada | Tecnologia | Descrição |
|---|---|---|
| **Backend** | PHP (7.4+ / 8.x) | Estruturado com sessões nativas (`session_start()`), controle de autenticação e permissões |
| **Banco de Dados** | MySQL / MariaDB | Acesso via PDO com consultas preparadas |
| **Frontend** | HTML5 + Tailwind CSS | Interface moderna, dark accents e microinterações responsivas |
| **Ícones & Tipografia** | FontAwesome 6 + Plus Jakarta Sans | Visual corporativo de alta segurança |

---

## 🗂️ 3. Estrutura de Arquivos

```text
cerbero/
├── assets/
│   ├── css/
│   │   └── custom.css             # Animações de toasts, modais e scrollbar
│   └── js/
│       └── cerbero.js             # Framework JS para Toasts e Modais sem alertas nativos
├── backend/
│   ├── login_process.php          # Autenticação segura com password_verify
│   ├── logout.php                 # Destruição segura de sessão
│   ├── solicitar_process.php      # Processamento público de solicitação de acesso
│   ├── solicitacao_acao.php       # Aprovação (cria usuário) ou Rejeição de solicitação
│   ├── usuario_salvar.php         # Criação/Edição de usuários (apenas Administrador)
│   ├── usuario_excluir.php        # Exclusão segura de usuários
│   ├── registro_salvar.php        # Criação/Edição de registros e ativos
│   └── registro_excluir.php       # Exclusão de registros
├── config/
│   ├── auth.php                   # Controle de sessão, permissões e toasts
│   └── bd.php                     # Atalho de conexão
├── includes/
│   ├── header.php                 # Cabeçalho HTML, CDN Tailwind e FontAwesome
│   ├── navbar.php                 # Topbar com status do sistema e perfil
│   ├── sidebar.php                # Menu lateral Mobile First com perfil e badges
│   ├── modals.php                 # Modais customizados reutilizáveis
│   ├── toast.php                  # Renderizador de mensagens flash TOAST
│   └── footer.php                 # Rodapé, créditos e scripts
├── bd.php                         # Arquivo central de conexão PDO com o MySQL
├── cerbero.sql                    # Script de criação de banco, tabelas e seeds
├── index.php                      # Dashboard administrativo principal
├── login.php                      # Tela de login moderna com atalhos de teste
├── solicitar_acesso.php           # Formulário público para pedido de acesso
├── usuarios.php                   # Gestão completa de usuários (CRUD)
├── registros.php                  # Gestão de registros, equipamentos e ativos
├── solicitacoes.php               # Painel de triagem de solicitações de acesso
├── preview.html                   # Demonstração estática e interativa para navegadores
└── README.md                      # Documentação completa
```

---

## 🔑 4. Credenciais de Acesso para Teste

Ao importar o arquivo `cerbero.sql`, as seguintes contas de teste estarão disponíveis:

| E-mail | Senha Padrão | Nível de Acesso | Departamento |
|---|---|---|---|
| `admin@cerbero.com` | `admin123` | **Administrador** | Segurança da Informação |
| `carlos@cerbero.com` | `carlos123` | **Gerente** | Operações & Infraestrutura |
| `ana@cerbero.com` | `ana123` | **Operador** | Atendimento ao Cliente |

*(Dica: na tela `login.php`, há botões de preenchimento rápido que inserem essas credenciais com um clique).*

---

## 🚀 5. Como Executar o Projeto

### Opção A: Em Servidor Local (XAMPP / Laragon / WAMP)
1. Copie a pasta `cerbero` para o diretório raiz do seu servidor web (ex: `C:\xampp\htdocs\cerbero`).
2. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`) e importe o script `cerbero.sql`.
3. Se necessário, ajuste as credenciais do banco em `bd.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cerbero');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Acesse no navegador: `http://localhost/cerbero`

### Opção B: Hospedagem InfinityFree / Servidor Web Remoto
1. No painel de controle do InfinityFree (ou cPanel), acesse a ferramenta **MySQL Databases** e crie a base de dados.
2. Acesse o **phpMyAdmin** do provedor e importe o arquivo `cerbero.sql`.
3. No arquivo `bd.php`, configure os dados fornecidos pelo InfinityFree (ex: `sqlXXX.infinityfree.com`, usuário `if0_XXXX`, senha da conta).
4. Envie os arquivos do projeto para o diretório `htdocs` via FTP (FileZilla) ou Gerenciador de Arquivos.

### Opção C: Visualização Imediata no Navegador (Preview Standalone)
- Abra o arquivo `preview.html` diretamente em qualquer navegador moderno (Google Chrome, Edge, Firefox).
- Permite avaliar a interface, responsividade, disparar os componentes **Toast** e testar os **Modais**, mesmo sem um servidor PHP/MySQL ativo no momento.
