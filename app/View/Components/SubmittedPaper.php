<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubmittedPaper extends Component
{
    public $paper;

    public function __construct($paper)
    {
        $this->paper = $paper;
    }

    public function render()
    {
        return view('components.submitted-paper');
    }
}