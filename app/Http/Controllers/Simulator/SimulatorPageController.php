<?php

namespace App\Http\Controllers\Simulator;

use App\Http\Controllers\Controller;

class SimulatorPageController extends Controller
{
    public function index()
    {
        return view('simulator.index');
    }
}
