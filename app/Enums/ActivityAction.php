<?php

namespace App\Enums;

enum ActivityAction: string
{
    case USER_CREATED = 'user.created';
    case USER_UPDATED = 'user.updated';
    case USER_TOGGLED = 'user.toggled';
    case CATEGORY_CREATED = 'category.created';
    case CATEGORY_UPDATED = 'category.updated';
    case CATEGORY_TOGGLED = 'category.toggled';
    case MATERIAL_CREATED = 'material.created';
    case MATERIAL_UPDATED = 'material.updated';
    case MATERIAL_PUBLISHED = 'material.published';
    case MATERIAL_ARCHIVED = 'material.archived';
    case VERSION_CREATED = 'version.created';
    case MATERIAL_DOWNLOADED = 'material.downloaded';
    case MATERIAL_PREVIEWED = 'material.previewed';
    case AUTH_LOGIN = 'auth.login';
    case AUTH_LOGOUT = 'auth.logout';

    public function label(): string
    {
        return match ($this) {
            self::USER_CREATED => 'Usuário criado',
            self::USER_UPDATED => 'Usuário atualizado',
            self::USER_TOGGLED => 'Status do usuário alterado',
            self::CATEGORY_CREATED => 'Categoria criada',
            self::CATEGORY_UPDATED => 'Categoria atualizada',
            self::CATEGORY_TOGGLED => 'Status da categoria alterado',
            self::MATERIAL_CREATED => 'Material criado',
            self::MATERIAL_UPDATED => 'Material atualizado',
            self::MATERIAL_PUBLISHED => 'Material publicado',
            self::MATERIAL_ARCHIVED => 'Material arquivado',
            self::VERSION_CREATED => 'Versão criada',
            self::MATERIAL_DOWNLOADED => 'Material baixado',
            self::MATERIAL_PREVIEWED => 'Prévia do material consultada',
            self::AUTH_LOGIN => 'Login realizado',
            self::AUTH_LOGOUT => 'Logout realizado',
        };
    }
}
