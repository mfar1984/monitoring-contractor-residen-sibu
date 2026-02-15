<?php

namespace App\View\Components\Layout;

use Illuminate\View\Component;
use Illuminate\View\View;

class Footer extends Component
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.layout.footer');
    }
}
