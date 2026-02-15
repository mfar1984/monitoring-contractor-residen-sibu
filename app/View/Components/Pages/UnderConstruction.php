<?php

namespace App\View\Components\Pages;

use Illuminate\View\Component;
use Illuminate\View\View;

class UnderConstruction extends Component
{
    public string $pageName;

    /**
     * Create a new component instance.
     */
    public function __construct(string $pageName = 'This Page')
    {
        $this->pageName = $pageName;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.pages.under-construction');
    }
}
