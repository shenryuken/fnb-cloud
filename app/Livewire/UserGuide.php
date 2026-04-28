<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('User Guide')]
class UserGuide extends Component
{
    public string $activeSection = 'getting-started';

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function sections(): array
    {
        return [
            'getting-started' => [
                'title' => 'Getting Started',
                'icon'  => 'rocket-launch',
            ],
            'shifts' => [
                'title' => 'Shift Management',
                'icon'  => 'banknotes',
            ],
            'pos' => [
                'title' => 'POS & Taking Orders',
                'icon'  => 'shopping-cart',
            ],
            'orders' => [
                'title' => 'Order Management',
                'icon'  => 'clipboard-list',
            ],
            'kds' => [
                'title' => 'Kitchen Display (KDS)',
                'icon'  => 'fire',
            ],
            'menu' => [
                'title' => 'Menu Management',
                'icon'  => 'layers',
            ],
            'loyalty' => [
                'title' => 'Loyalty Program',
                'icon'  => 'star',
            ],
            'reports' => [
                'title' => 'Reports',
                'icon'  => 'chart-bar',
            ],
            'settings' => [
                'title' => 'Settings',
                'icon'  => 'cog-6-tooth',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.user-guide', [
            'sections' => $this->sections(),
        ]);
    }
}
