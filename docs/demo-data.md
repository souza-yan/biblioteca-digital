# Dados de demonstração

Os dados descritos aqui são fictícios e destinados a ambientes locais de desenvolvimento. O seeder não é executado pelo `DatabaseSeeder` padrão.

## Contas

Todas as contas usam a senha `DemoRobotics2026!`.

| Perfil | E-mail | Uso |
| --- | --- | --- |
| ADMIN | `demo.admin@example.com` | Administração global |
| STAFF — Norte | `demo.gestao.norte@example.com` | Gestão da EMEF Demonstração Norte |
| STAFF — Centro | `demo.gestao.centro@example.com` | Gestão da EMEF Demonstração Centro |
| STAFF — Sul | `demo.gestao.sul@example.com` | Gestão da EMEF Demonstração Sul |
| STAFF — Vale | `demo.gestao.vale@example.com` | Gestão da EMEF Demonstração do Vale |
| TEACHER | `demo.professor.multiescola@example.com` | Professor vinculado às escolas Norte e Centro |

As outras contas TEACHER são:

- Norte: `demo.professor.norte.02@example.com`, `demo.professor.norte.03@example.com`, `demo.professor.norte.04@example.com`, `demo.professor.norte.05@example.com`.
- Centro: `demo.professor.centro.02@example.com`, `demo.professor.centro.03@example.com`, `demo.professor.centro.04@example.com`, `demo.professor.centro.05@example.com`.
- Sul: `demo.professor.sul.01@example.com`, `demo.professor.sul.02@example.com`, `demo.professor.sul.03@example.com`, `demo.professor.sul.04@example.com`, `demo.professor.sul.05@example.com`.
- Vale: `demo.professor.vale.01@example.com`, `demo.professor.vale.02@example.com`, `demo.professor.vale.03@example.com`, `demo.professor.vale.04@example.com`, `demo.professor.vale.05@example.com`.

Todos os endereços usam o domínio reservado `example.com`.

## Telas para validação manual

- ADMIN: dashboard, usuários, materiais, categorias e atividades.
- STAFF: dashboard, usuários e atividades da escola vinculada; materiais e categorias compartilhados conforme as permissões atuais.
- TEACHER: biblioteca, detalhes de materiais publicados, favoritos e downloads autorizados.
- Professor multiescola: entre como cada STAFF de sua escola e confira que o vínculo exibido/administrado é somente o vínculo local.

Este seeder não cria materiais nem arquivos. Para verificar biblioteca, prévia e download, o banco precisa já conter materiais publicados com versões e arquivos correspondentes.

## Execução

O comando abaixo executa somente o seeder de demonstração:

```bash
php artisan db:seed --class='Database\Seeders\DemoDataSeeder'
```

Antes de executar, confirme que o ambiente é `local` e que a conexão e o banco selecionados são próprios para desenvolvimento ou descartáveis. O seeder recusa ambientes diferentes de `local` e `testing`. Ele atualiza somente as escolas e contas com os nomes/e-mails de demonstração acima. A cada execução, preserva as linhas de vínculo existentes dessas contas, marca os vínculos inativos e ativa os vínculos previstos para a demonstração; não remove registros nem modifica contas fora desse conjunto.

O seeder é idempotente: escolas e usuários são localizados por nomes/e-mails estáveis e os vínculos são atualizados sem duplicar as chaves da pivô. Ele não cria materiais nem arquivos.
