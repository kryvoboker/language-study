<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiProviders\Pages;

use App\Filament\Resources\AiProviders\AiProviderSettingResource;
use App\Models\AiProviderSetting;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class EditAiProviderSetting extends EditRecord
{
	protected static string                        $resource = AiProviderSettingResource::class;
	#[Locked]
	public int|string|Model|null|AiProviderSetting $record   = null;

	protected function getHeaderActions(): array
	{
		$actions = [
			Action::make('save')
				->label(__('admin/default.buttons.save'))
				->icon(Heroicon::CheckCircle)
				->action(fn() => $this->save()),
			$this->getCancelFormAction()
				->label(__('admin/default.buttons.cancel'))
				->icon(Heroicon::ArrowLeftOnRectangle),
		];

		if ($this->record->is_default === false) {
			$actions[] = DeleteAction::make('delete')
				->label(__('admin/default.buttons.delete'))
				->icon(Heroicon::Trash);
		}

		return $actions;
	}
}