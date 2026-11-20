<?php

namespace App\Http\Controllers\MapsCalculator;

use App\Http\Controllers\Controller;
use App\Models\MapsCalculator\Project;

class MapulatorProjectController extends Controller
{
    public function show(Project $project)
    {
        return view('mapscalculator.project-show', compact('project'));
    }
}
