# Changelog — Cozinha Guiada

Este arquivo registra alterações relevantes, decisões técnicas e revisões realizadas no projeto. As datas abaixo seguem os registros disponíveis no GitHub e nos documentos do projeto.

## 2026-11-13 — Consolidação da documentação de entrega

- Reunida a especificação técnica na raiz do repositório em `spec.md`, mantendo a especificação original dentro de `Cozinha Guiada/`.
- Documentados ambiente, dependências, instalação, variáveis de ambiente, contas de demonstração, limitações e estrutura real do banco.
- Criado exemplo de configuração sem credenciais reais.
- Adicionadas regras de exclusão para arquivos locais e mídias enviadas.
- Adicionadas regras Apache para encaminhar rotas ao Front Controller e impedir acesso/execução de scripts na pasta de uploads.
- Registrado que a conta `rafaelpitwak` possui permissão de escrita no repositório, conforme consulta ao GitHub.

## Decisões técnicas registradas no desenvolvimento

- Mantida a stack PHP + MySQL/MariaDB + Apache/XAMPP, em alinhamento com o escopo acadêmico original.
- Organizada a aplicação em arquitetura MVC, com controladores, modelos, serviços, middleware, utilitários e views.
- Utilizado PDO com prepared statements para consultas ao banco.
- Adotados quatro perfis de acesso: visitante, cadastrado, produtor e administrador.
- Implementado filtro combinado de compatibilidades alimentares com lógica estrita AND.
- Incluídos Modo Guiado, Screen Wake Lock, temporizador com Web Audio API, acessibilidade por teclado e estilos de impressão.
- Incluídos dados iniciais de demonstração e oito suítes de testes automatizados, conforme documentação do projeto.

## Revisão humana de propostas geradas por IA

A revisão humana não deve ser presumida. A equipe deve completar o registro abaixo para cada sugestão de IA que tenha influenciado o código ou a documentação:

| Data | Proposta/trecho analisado | Decisão humana (aceita, alterada ou rejeitada) | Justificativa | Responsável |
|---|---|---|---|---|
| A preencher | A preencher | A preencher | A preencher | A preencher |

**Nota:** os arquivos e commits atuais não identificam de forma confiável todas as propostas de IA nem quem as aprovou. Nenhuma aprovação foi inventada neste changelog. A equipe deve completar a tabela após revisar o conteúdo efetivamente utilizado.

## Observações

- O arquivo `Cozinha Guiada/database/schema.sql` contém `DROP TABLE IF EXISTS`; sua execução apaga e recria as tabelas. Faça backup antes de executar em uma base com dados.
- As credenciais de demonstração estão no `seed.sql`; são apenas para teste local e devem ser alteradas antes de qualquer implantação pública.
- A URL pública de produção ainda precisa ser informada e testada para encerrar a entrega.
