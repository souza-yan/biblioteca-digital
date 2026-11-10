# Dados iniciais e demonstração

As quatro escolas participantes e seus gestores são cadastrados por `ParticipatingSchoolsSeeder`. Os dados adicionais de demonstração continuam restritos aos ambientes `local` e `testing` por `CaraguatatubaSchoolsSeeder`.

## Escolas participantes

As escolas são definidas em uma única lista, `SCHOOLS`, no `ParticipatingSchoolsSeeder`. Cada escola recebe um gestor inicial. Endereço e região ficam a definir até o cadastro dos dados oficiais.

| Escola | Tipo | Região | Endereço |
| --- | --- | --- | --- |
| EMEF Profa Maria Aparecida de Carvalho | EMEF | A definir | A definir |
| EMEF Luiz Silvar | EMEF | A definir | A definir |
| EMEF Profa Maria Thereza de Souza Castro | EMEF | A definir | A definir |
| EMEF Luiz Ribeiro Muniz | EMEF | A definir | A definir |

Os nomes foram informados para implantação; regiões e endereços ainda precisam ser preenchidos. Se existirem os antigos cadastros Escola A a D, o seeder reaproveita seus IDs e vínculos, sem apagar usuários nem históricos.

## Contas iniciais

As contas novas começam com a senha `Atividade1!`, centralizada em `config/accounts.php`. Professores e gestores precisam definir uma senha pessoal no primeiro acesso antes de usar o sistema; administradores estão dispensados. Restaurar a senha padrão volta a exigir a troca para professores e gestores.

O seeder preserva senhas já alteradas, CPFs, dados de contato e contas desativadas. Não inventa CPFs: o administrador deve regularizar esse dado antes de ativar `CPF_LOGIN_ENABLED=true`.

| Perfil | Nome | Escola | E-mail |
| --- | --- | --- | --- |
| Administrador | Administrador | — | `admin@example.com` |
| Gestão | Gestão - EMEF Profa Maria Aparecida de Carvalho | Maria Aparecida | `mariaaparecida@educacaocaraguatatuba.com.br` |
| Gestão | Gestão - EMEF Luiz Silvar | Luiz Silvar | `luizsilvar@educacaocaraguatatuba.com.br` |
| Gestão | Gestão - EMEF Profa Maria Thereza de Souza Castro | Maria Thereza | `mariathereza@educacaocaraguatatuba.com.br` |
| Gestão | Gestão - EMEF Luiz Ribeiro Muniz | Luiz Ribeiro Muniz | `luizribeiromuniz@educacaocaraguatatuba.com.br` |
| Professor (demonstração) | Professor - EMEF Profa Maria Aparecida de Carvalho | Maria Aparecida | `professor.a@educacaocaraguatatuba.com.br` |
| Professor (demonstração) | Professor - EMEF Luiz Silvar | Luiz Silvar | `professor.b@educacaocaraguatatuba.com.br` |
| Professor (demonstração) | Professor - EMEF Profa Maria Thereza de Souza Castro | Maria Thereza | `professor.c@educacaocaraguatatuba.com.br` |
| Professor (demonstração) | Professor - EMEF Luiz Ribeiro Muniz | Luiz Ribeiro Muniz | `professor.d@educacaocaraguatatuba.com.br` |

O seeder de implantação cria somente os quatro gestores. As contas de professor e administrador da tabela são criadas apenas pelo seeder local de demonstração. E-mails de professores existentes são preservados.

## Seeders executados

O `DatabaseSeeder` executa a sequência abaixo:

1. `CaraguatatubaSchoolsSeeder`: chama `ParticipatingSchoolsSeeder` e complementa com administrador e professores de demonstração.
2. `CategorySeeder`: categorias compartilhadas da biblioteca.
3. `MaterialSeeder`: materiais, versões e arquivos de exemplo, associados ao administrador.
4. `ActivityLogSeeder`: favoritos, downloads e registros de atividade usando contas de demonstração das duas primeiras escolas.

Ao ser executado, o seeder de demonstração `CaraguatatubaSchoolsSeeder` remove as 16 escolas fictícias anteriores e as contas antigas `gestao1`, `gestao2` e `professor1` a `professor3` do domínio `example.com`. Também remove favoritos, downloads e logs pertencentes a essas contas antes de excluí-las. Essa limpeza não é executada pelo `ParticipatingSchoolsSeeder`.

## Execução

Para criar uma base local completa do zero:

```bash
php artisan migrate:fresh --seed
```

Para executar todos os seeders em um banco já migrado:

```bash
php artisan db:seed
```

Para cadastrar somente as quatro escolas e gestores, sem executar a limpeza de demonstração ou criar contas de professor:

```bash
php artisan db:seed --class='Database\Seeders\ParticipatingSchoolsSeeder' --no-interaction
```

Para executar escolas e contas de demonstração (somente local/testing):

```bash
php artisan db:seed --class='Database\Seeders\CaraguatatubaSchoolsSeeder'
```

O processo é idempotente: preserva credenciais e vínculos existentes, sem gerar duplicidades. O seeder de implantação recusa sobrescrever uma conta de outro cargo ou vincular um gestor já ativo em outra escola. Use o seeder de demonstração somente em local/testing. Para os gestores de implantação, regularize os CPFs e conclua a troca obrigatória da senha inicial.
