<?php

namespace App\Livewire\Dashboard;

use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

class TagList extends Component
{
    use WithPagination;

    public function deleteTag(Tag $tag): void
    {
        $tag->delete();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.dashboard.tag-list', [
            'tags' => Tag::latest()->paginate(10),
        ])->layout('layouts.app');
    }
}
