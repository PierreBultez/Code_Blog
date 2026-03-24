<div
    x-data="{ value: @entangle($attributes->wire('model')) }"
    x-init="
        if (typeof tinymce === 'undefined') {
            let script = document.createElement('script');
            script.src = '{{ asset('js/tinymce/tinymce.min.js') }}';
            script.referrerPolicy = 'origin';
            script.onload = () => initTinyMCE();
            document.head.appendChild(script);
        } else {
            initTinyMCE();
        }

        function initTinyMCE() {
            tinymce.init({
                target: $refs.tinymce,
                license_key: 'gpl',
                promotion: false,
                plugins: 'codesample link lists autolink',
                toolbar: 'undo redo | blocks | fontfamily fontsize | bold italic blockquote | alignleft aligncenter alignright alignjustify | bullist numlist | link codesample | backcolor',
                setup: function (editor) {
                    editor.on('blur', function (e) {
                        value = editor.getContent()
                    });
                    editor.on('init', function (e) {
                        if (value != null) {
                            editor.setContent(value)
                        }
                    });
                    $watch('value', function (newValue) {
                        if (newValue !== editor.getContent()) {
                            editor.setContent(newValue || '')
                        }
                    });
                }
            });
        }
    "
    wire:ignore
    class="w-full"
>
    <textarea x-ref="tinymce"></textarea>
</div>
