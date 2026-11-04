# Dados iniciais e demonstração

Os dados deste documento são provisórios e existem para desenvolvimento e testes locais. O seeder de escolas e contas recusa execução fora dos ambientes `local` e `testing`.

## Escolas participantes

As escolas são definidas em uma única lista, `SCHOOLS`, no `CaraguatatubaSchoolsSeeder`. Para acrescentar uma escola, basta incluir uma definição nessa lista; o seeder cria ou atualiza a escola e as duas contas vinculadas.

| Escola | Tipo | Região | Endereço |
| --- | --- | --- | --- |
| Escola A | EMEF | Centro | A definir |
| Escola B | EMEF | Centro | A definir |
| Escola C | EMEF | Centro | A definir |
| Escola D | EMEF | Centro | A definir |

Os nomes, regiões e endereços devem ser substituídos pelos dados oficiais quando forem disponibilizados.

## Contas iniciais

Todas as contas abaixo são ativas, possuem e-mail verificado e usam a senha padrão `Atividade1!`. Esse valor está centralizado em `config/accounts.php`.

| Perfil | Nome | Escola | E-mail |
| --- | --- | --- | --- |
| Administrador | Administrador | — | `admin@example.com` |
| Gestão | Gestão - Escola A | Escola A | `a@educacaocaraguatatuba.com.br` |
| Gestão | Gestão - Escola B | Escola B | `b@educacaocaraguatatuba.com.br` |
| Gestão | Gestão - Escola C | Escola C | `c@educacaocaraguatatuba.com.br` |
| Gestão | Gestão - Escola D | Escola D | `d@educacaocaraguatatuba.com.br` |
| Professor | Professor - Escola A | Escola A | `professor.a@educacaocaraguatatuba.com.br` |
| Professor | Professor - Escola B | Escola B | `professor.b@educacaocaraguatatuba.com.br` |
| Professor | Professor - Escola C | Escola C | `professor.c@educacaocaraguatatuba.com.br` |
| Professor | Professor - Escola D | Escola D | `professor.d@educacaocaraguatatuba.com.br` |

Há uma conta de gestão e uma conta de professor por escola. As contas de professor foram mantidas para que os dados de favoritos, downloads e atividades de demonstração continuem coerentes.

## Seeders executados

O `DatabaseSeeder` executa a sequência abaixo:

1. `CaraguatatubaSchoolsSeeder`: escolas e contas iniciais.
2. `CategorySeeder`: categorias compartilhadas da biblioteca.
3. `MaterialSeeder`: materiais, versões e arquivos de exemplo, associados ao administrador.
4. `ActivityLogSeeder`: favoritos, downloads e registros de atividade usando as novas contas da Escola A e da Escola B.

Ao ser executado, o seeder de escolas remove as 16 escolas fictícias anteriores e as contas antigas `gestao1`, `gestao2` e `professor1` a `professor3` do domínio `example.com`. Também remove favoritos, downloads e logs pertencentes a essas contas antes de excluí-las.

## Execução

Para criar uma base local completa do zero:

```bash
php artisan migrate:fresh --seed
```

Para executar todos os seeders em um banco já migrado:

```bash
php artisan db:seed
```

Para executar somente escolas e contas iniciais:

```bash
php artisan db:seed --class='Database\Seeders\CaraguatatubaSchoolsSeeder'
```

O processo é idempotente: escolas e usuários são localizados por nome ou e-mail, e os vínculos são sincronizados sem gerar duplicidades. Como as contas possuem uma senha padrão pública, não execute esses seeders em produção.
