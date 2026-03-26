<x-mail::message>
# Nouveau message de contact

**De :** {{ $contactData['name'] }} ({{ $contactData['email'] }})

**Sujet :** {{ $contactData['subject'] }}

---

{{ $contactData['message'] }}

<x-mail::button :url="'mailto:' . $contactData['email']">
Répondre à {{ $contactData['name'] }}
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
