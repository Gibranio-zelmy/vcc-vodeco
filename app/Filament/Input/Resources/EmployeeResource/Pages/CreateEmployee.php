<?php

namespace App\Filament\Input\Resources\EmployeeResource\Pages;

use App\Filament\Input\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
}
