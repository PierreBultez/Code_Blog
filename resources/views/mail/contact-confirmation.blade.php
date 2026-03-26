<x-mail::message>
# Merci pour votre message, {{ $contactData['name'] }} !

Votre message a bien été reçu. Je vous répondrai dans les meilleurs délais.

**Récapitulatif :**

- **Sujet :** {{ $contactData['subject'] }}
- **Message :** {{ $contactData['message'] }}

Merci,<br>
Pierre — {{ config('app.name') }}
</x-mail::message>
