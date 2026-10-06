<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  string  $canvas  the page's ground: "paper", or "linen" for a
     *                          page of cards (the overview), so each card
     *                          stands on something a shade darker than itself
     */
    public function __construct(public string $canvas = 'paper') {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
