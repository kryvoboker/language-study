[← Deployment](deployment.md) · [Back to README](../README.md)

# Filament resources

The admin panel uses Filament 5 resources. Each resource keeps its model, navigation metadata, labels, and page routes, while dedicated classes build the form schema and table configuration.

## Current structure

Resource classes expose Filament's `form()` and `table()` entry points and delegate their configuration to dedicated builders:

```text
httpdocs/backend/app/Filament/Resources/
├── AiProviders/
│   ├── AiProviderSettingResource.php
│   ├── Pages/
│   ├── Schemas/AiProviderSettingForm.php
│   └── Tables/AiProviderSettingTable.php
└── Users/Users/
    ├── UserResource.php
    ├── Pages/
    ├── Schemas/UserForm.php
    └── Tables/UserTable.php
```

The page classes extend Filament's `CreateRecord`, `EditRecord`, and `ListRecords` pages. They provide the resource binding and page actions; they do not duplicate form or table definitions.

## Schema builders

Schema builders receive a `Filament\Schemas\Schema` instance and return the configured schema from a `configure()` method:

```php
public static function form(Schema $schema): Schema
{
    return UserForm::configure($schema);
}
```

The current builders are:

| Builder | Resource | Main fields |
|---------|----------|-------------|
| `AiProviderSettingForm` | `AiProviderSettingResource` | Name, provider key, prompt instruction, enabled/default toggles, encrypted configuration |
| `UserForm` | `UserResource` | Name, email, blocked toggle, roles, optional AI provider |

The AI provider form's `prompt_instruction` field contains the instructions sent to the selected AI assistant. It is required and replaces the default system prompt. Provider configuration is edited through a generic key/value field and stored encrypted by the application.

## Table builders

Table builders receive a `Filament\Tables\Table` instance and return the configured table from a `configure()` method:

```php
public static function table(Table $table): Table
{
    return UserTable::configure($table);
}
```

The current builders are:

| Builder | Resource | Columns and actions |
|---------|----------|---------------------|
| `AiProviderSettingTable` | `AiProviderSettingResource` | Name, provider key badge, enabled/default indicators, edit action |
| `UserTable` | `UserResource` | Searchable/sortable name, searchable email, role badges, blocked indicator, creation date, edit/delete actions |

## Extension guidelines

- Add shared form fields to the relevant class in `Schemas/`.
- Add columns and row actions to the relevant class in `Tables/`.
- Keep resource classes focused on model metadata, navigation, labels, routes, and global search behavior.
- Keep page-specific behavior in the corresponding `Pages/` class, such as header actions or lifecycle hooks.
- Use Filament's `Filament\Schemas\Schema` type for resource forms and `Filament\Tables\Table` for tables.

This separation keeps reusable resource configuration in one place while leaving pages free to add behavior specific to create, edit, or list workflows.

## See Also

- [Architecture](architecture.md) — application boundaries and directory structure
- [Admin Authorization](admin-authorization.md) — roles, permissions, and panel access
- [AI Providers](ai-providers.md) — provider settings and runtime integration
