<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Optional body class and page title to pass to the layout.
     */
    public function __construct(
        public ?string $pageClass = null,
        public ?string $pageTitle = null,
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app', [
            'pageClass' => $this->pageClass,
            'pageTitle' => $this->pageTitle,
        ]);
    }
}
