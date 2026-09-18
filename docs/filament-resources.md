[← Architecture](architecture.md) · [Back to README](../README.md)

# Filament resources

Filament resource pages use dedicated schema and table builder classes.

- `Schemas/*Schema` classes build forms and are called by `CreateRecord`, `EditRecord`, and, when needed, `ListRecords` pages.
- `Tables/*Table` classes build tables and are called by `ListRecords` pages.
- Pages still own the `form(Schema $schema)` and `table(Table $table)` entry points, so page-specific behavior can be added without putting UI code back into a resource.
- Resource classes contain the model, navigation, labels, authorization-related metadata, and page routes. They do not build forms or tables.

This keeps page-specific behavior close to the Livewire page that renders it and makes it possible to customize create, edit, and list experiences independently without adding conditionals to a resource class.
