<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EditUserForm extends Component
{
    public $user; // Property to hold the user data

    /**
     * Create a new component instance.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user; // Assign the user data to the property
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.edit-user-form');
    }
}