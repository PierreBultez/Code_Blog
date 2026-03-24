<?php

namespace App\Livewire\Dashboard;

use App\Models\Tag;
use Livewire\Component;

class TagForm extends Component
{
    public ?Tag $tag = null;

    public string $name = '';

    public function mount(?Tag $tag = null): void
    {
        if ($tag && $tag->exists) {
            $this->tag = $tag;
            $this->name = $tag->name;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        if (! $this->tag) {
            $this->tag = new Tag;
        }

        $this->tag->name = $this->name;
        $this->tag->save();

        $this->redirectRoute('dashboard.tags.index');
    }

    public function render()
    {
        return view('livewire.dashboard.tag-form')->layout('layouts.app');
    }
}
