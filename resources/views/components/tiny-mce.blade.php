<div
    x-data="tinyMceEditor('{{ $attributes->wire('model')->value() }}')"
    wire:ignore
    class="w-full"
    data-upload-url="{{ route('tinymce.upload') }}"
    data-asset-url="{{ asset('js/tinymce/tinymce.min.js') }}"
>
    <textarea x-ref="tinymce"></textarea>
</div>
