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
                plugins: 'codesample link lists autolink emoticons fullscreen image',
                toolbar: 'undo redo | blocks | emoticons image | bold italic blockquote | alignleft aligncenter alignright alignjustify | bullist numlist | link codesample | backcolor | fullscreen',
                images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    fetch('{{ route('tinymce.upload') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                        },
                        body: formData,
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Upload failed') });
                        }
                        return response.json();
                    })
                    .then(data => resolve(data.location))
                    .catch(err => reject(err.message));
                }),
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
