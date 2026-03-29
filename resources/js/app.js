// Register Alpine components before Alpine starts (CSP-safe: complex logic lives here, not in HTML attributes)
document.addEventListener('alpine:init', () => {

    // Action message with auto-dismiss (used inside Livewire components)
    Alpine.data('actionMessage', (eventName) => ({
        shown: false,
        timeout: null,
        init() {
            this.$wire.$on(eventName, () => {
                clearTimeout(this.timeout);
                this.shown = true;
                this.timeout = setTimeout(() => { this.shown = false; }, 2000);
            });
        },
    }));

    // Clipboard copy with visual feedback
    Alpine.data('clipboardCopy', () => ({
        copied: false,
        copy(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 1500);
            }).catch(() => {
                console.warn('Could not copy to clipboard');
            });
        },
    }));

    // Two-factor challenge input toggle (auth page)
    Alpine.data('twoFactorChallenge', (hasRecoveryError) => ({
        showRecoveryInput: hasRecoveryError,
        code: '',
        recovery_code: '',
        toggleInput() {
            this.showRecoveryInput = !this.showRecoveryInput;
            this.code = '';
            this.recovery_code = '';
            this.$dispatch('clear-2fa-auth-code');
            this.$nextTick(() => {
                if (this.showRecoveryInput) {
                    this.$refs.recovery_code?.focus();
                } else {
                    this.$dispatch('focus-2fa-auth-code');
                }
            });
        },
    }));

    // TinyMCE editor with Livewire integration
    Alpine.data('tinyMceEditor', (wireModelName) => ({
        init() {
            const component = this;
            const uploadUrl = this.$el.dataset.uploadUrl;
            const assetUrl = this.$el.dataset.assetUrl;
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

            const initEditor = () => {
                tinymce.init({
                    target: component.$refs.tinymce,
                    license_key: 'gpl',
                    promotion: false,
                    plugins: 'codesample link lists autolink emoticons fullscreen image',
                    toolbar: 'undo redo | blocks | emoticons image | bold italic blockquote | alignleft aligncenter alignright alignjustify | bullist numlist | link codesample | backcolor | fullscreen',
                    images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());

                        fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: formData,
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => { throw new Error(err.message || 'Upload failed'); });
                            }
                            return response.json();
                        })
                        .then(data => resolve(data.location))
                        .catch(err => reject(err.message));
                    }),
                    setup: (editor) => {
                        editor.on('blur', () => {
                            component.$wire.set(wireModelName, editor.getContent());
                        });
                        editor.on('init', () => {
                            const content = component.$wire.get(wireModelName);
                            if (content != null) {
                                editor.setContent(content);
                            }
                        });
                    },
                });
            };

            if (typeof tinymce === 'undefined') {
                const script = document.createElement('script');
                script.src = assetUrl;
                script.referrerPolicy = 'origin';
                script.onload = initEditor;
                document.head.appendChild(script);
            } else {
                initEditor();
            }
        },
    }));

});
