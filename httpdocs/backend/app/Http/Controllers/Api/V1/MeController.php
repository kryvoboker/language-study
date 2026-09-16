<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): array
    {
        return ['id' => $request->user()->id, 'name' => $request->user()->name, 'email' => $request->user()->email];
    }
}
