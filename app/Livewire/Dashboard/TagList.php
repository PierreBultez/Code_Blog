<?php

namespace App\Livewire\Dashboard;

use App\Models\Tag;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class TagList extends Component
{
    use WithPagination;

    public function deleteTag(Tag $tag): void
    {
        Log::channel('single')->info('Tag deleted', [
            'tag_id' => $tag->id,
            'name' => $tag->name,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

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
