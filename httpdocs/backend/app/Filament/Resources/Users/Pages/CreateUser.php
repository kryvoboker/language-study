<?php
namespace httpdocs\backend\app\Filament\Resources\Users\Pages;
use httpdocs\backend\app\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
class CreateUser extends CreateRecord { protected static string $resource = UserResource::class; }