<?php

namespace Modules\Order\UI\ViewComponents;

use Illuminate\View\Component;
use Illuminate\View\View;

class Alert extends Component
{
    public function __construct(public string $message) {}

    public function render(): View
    {
        return view('order::alert');
    }
}
