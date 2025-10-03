Mini Sistema de Gestão de Produtos Este é o repositório oficial do projeto "Mini Sistema de Gestão de Produtos", desenvolvido como requisito para a disciplina [Nome da Disciplina]. O sistema foi construído utilizando PHP orientado a objetos, MySQL com PDO e tecnologias frontend modernas para criar uma aplicação web funcional para gerenciamento de produtos, fornecedores e cestas de compras.

Integrantes da Equipe Nome Completo RA Guilherme Selestriano RA:60300633 Keven Hattori Brito RA:60300837 Joao Mazini RA:60000173

Exportar para as Planilhas Funcionalidades Principais O sistema oferece um conjunto robusto de funcionalidades para uma gestão simplificada:

Autenticação de Usuários: Sistema seguro de login e cadastro, com senhas armazenadas utilizando hash.

Cadastro de Produtos: Interface para criar, visualizar, editar e excluir produtos, associando cada um a um fornecedor.

Cadastro de Fornecedores: Gerenciamento completo dos fornecedores.

Gestão com AJAX: Uma área dedicada para gerenciar produtos e fornecedores de forma dinâmica, sem a necessidade de recarregar a página, proporcionando uma experiência de usuário mais fluida.

Montagem de Cesta: Tela para visualização de todos os produtos, permitindo a seleção de múltiplos itens para adicionar a uma cesta de compras.

Visualização da Cesta: Resumo detalhado da cesta com a lista de produtos selecionados, a quantidade total de itens e o valor total da compra.

Tecnologias Utilizadas Este projeto foi construído com as seguintes tecnologias:

Backend:

PHP 8+ (com Orientação a Objetos)

PDO (PHP Data Objects) para conexão segura com o banco de dados

Banco de Dados:

MySQL

Frontend:

HTML5

CSS3

JavaScript (ES6+)

Bootstrap 5 (ou Tailwind CSS, dependendo da sua escolha)

AJAX (via API Fetch)

Ferramentas de Design e Modelagem:

Figma (para prototipagem e design de interface)

MySQL Workbench (para modelagem do banco de dados)

Versionamento:

Git & GitHub

Como Executar o Projeto Siga os passos abaixo para configurar e executar o projeto em um ambiente de desenvolvimento local.

Pré-requisitos Um servidor web local (XAMPP, WAMP, MAMP ou o servidor embutido do PHP)

PHP 8 ou superior

MySQL ou MariaDB

Git instalado na sua máquina

Passo a Passo Clone o Repositório:

Bash

git clone [URL_DO_SEU_REPOSITORIO_GIT] cd mini_sistema_produtos Configuração do Banco de Dados:

Abra o seu cliente MySQL (phpMyAdmin, DBeaver, etc.).

Crie um novo banco de dados. Recomendamos o nome gestao_produtos_db.

Edite o arquivo /backend/config/Database.php e insira as suas credenciais de acesso ao banco de dados (host, nome do banco, usuário e senha).

Criação das Tabelas:

Abra o navegador e acesse o script de setup para criar automaticamente todas as tabelas necessárias:

http://localhost/mini_sistema_produtos/setup.php Após a execução, uma mensagem de sucesso deverá ser exibida. Por segurança, você pode deletar o arquivo setup.php após a criação do banco.

Inicie o Servidor:

Navegue até a pasta raiz do projeto e inicie o servidor embutido do PHP (ou utilize o seu servidor local):

Bash

php -S localhost:8000 Acesse a Aplicação:

Abra seu navegador e acesse http://localhost:8000.

Pronto! Agora você pode se cadastrar e testar todas as funcionalidades do sistema.

Design de Interface (Figma) Os esboços e o design da interface do usuário foram criados no Figma, focando em uma experiência limpa, intuitiva e consistente. Abaixo estão algumas das telas principais projetadas.

Substitua os blocos abaixo pelas imagens exportadas do seu projeto no Figma.

Tela de Login e Cadastro Dashboard e Cadastro de Produtos Tela de Montagem da Cesta
Diagrama Entidade-Relacionamento (DER) A estrutura do banco de dados foi modelada para garantir a integridade e o relacionamento correto entre as entidades do sistema.

Substitua a imagem abaixo pelo diagrama exportado do MySQL Workbench.
