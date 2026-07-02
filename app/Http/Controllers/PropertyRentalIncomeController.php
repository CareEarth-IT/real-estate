<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PropertyRentalIncomeController extends Controller
{
    public function index(): View
    {
        return view('property.rental-income.index');
    }
}
