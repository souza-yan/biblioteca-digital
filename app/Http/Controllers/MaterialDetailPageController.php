<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Contracts\View\View;

class MaterialDetailPageController extends Controller
{
    public function __invoke(Material $material): View
    {
        return view('materials.show', ['material' => $material]);
    }
}
