<x-mail::message>
# Nouveau commentaire

**{{ $comment->author_name }}** a commenté l'article **{{ $comment->article->title }}** :

---

{{ Str::limit($comment->content, 300) }}

<x-mail::button :url="route('articles.show', $comment->article)">
Voir l'article
</x-mail::button>

<x-mail::button :url="route('dashboard.comments.index')">
Modérer les commentaires
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
