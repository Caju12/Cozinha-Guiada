# Cozinha Guiada

Plataforma web de culinária inclusiva, desenvolvida em PHP, JavaScript, HTML/CSS e MySQL/MariaDB para apoiar pessoas iniciantes na cozinha.

## Onde está o projeto

O código-fonte está na pasta **[`Cozinha Guiada/`](Cozinha%20Guiada/)**. A documentação técnica final está em [`spec.md`](spec.md).

- [README de instalação e uso](Cozinha%20Guiada/README.md)
- [Especificação técnica e funcional](spec.md)
- [Banco de dados — schema.sql](Cozinha%20Guiada/database/schema.sql)
- [Dados iniciais — seed.sql](Cozinha%20Guiada/database/seed.sql)
- [Checklist de testes de aceite](Cozinha%20Guiada/tests/acceptance_checklist.md)
- [Histórico de alterações](CHANGELOG.md)

## Instalação rápida

1. Clone o repositório e entre na pasta `Cozinha Guiada/`.
2. Copie `.env.example` para `.env` e configure a conexão com MySQL/MariaDB.
3. Inicie o banco e execute `php database/setup.php`.
4. Inicie o servidor local com `php -S 127.0.0.1:8000 -t public public/index.php`.
5. Acesse [http://127.0.0.1:8000](http://127.0.0.1:8000).

Consulte o README interno e o `spec.md` para os detalhes completos. O script de instalação recria as tabelas; não o execute sobre uma base com dados que deseja preservar.
