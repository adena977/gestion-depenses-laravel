<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // CORRECTION : Utiliser un layout différent qui n'appelle pas le component
        // 'layouts.application' au lieu de 'layouts.app'
        return view('layouts.application');
    }
}