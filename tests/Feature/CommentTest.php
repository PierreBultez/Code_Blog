<?php

use App\Livewire\ArticleComments;
use App\Livewire\Dashboard\CommentList;
use App\Mail\NewCommentMail;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

it('displays the comment section on an article page', function () {
    $article = Article::factory()->create(['is_published' => true]);

    $this->get(route('articles.show', $article))
        ->assertSuccessful()
        ->assertSeeLivewire(ArticleComments::class);
});

it('can post a comment with pseudo and content', function () {
    Mail::fake();
    $article = Article::factory()->create(['is_published' => true]);

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->set('author_name', 'JeanTest')
        ->set('content', 'Super article, merci !')
        ->call('addComment')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSet('content', '');

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'author_name' => 'JeanTest',
        'content' => 'Super article, merci !',
        'is_read' => false,
    ]);
});

it('displays existing comments on the article page', function () {
    $article = Article::factory()->create(['is_published' => true]);
    Comment::factory()->create([
        'article_id' => $article->id,
        'author_name' => 'Alice',
        'content' => 'Un commentaire visible',
    ]);

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->assertSee('Alice')
        ->assertSee('Un commentaire visible');
});

it('validates required fields', function () {
    $article = Article::factory()->create(['is_published' => true]);

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->set('author_name', '')
        ->set('content', '')
        ->call('addComment')
        ->assertHasErrors(['author_name', 'content']);
});

it('validates max length for author_name and content', function () {
    $article = Article::factory()->create(['is_published' => true]);

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->set('author_name', str_repeat('a', 51))
        ->set('content', str_repeat('a', 2001))
        ->call('addComment')
        ->assertHasErrors(['author_name', 'content']);
});

it('rejects comments when honeypot is filled', function () {
    Mail::fake();
    $article = Article::factory()->create(['is_published' => true]);

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->set('author_name', 'SpamBot')
        ->set('content', 'Buy cheap stuff!')
        ->set('honeypot', 'bot-filled-this')
        ->call('addComment')
        ->assertSet('sent', true);

    $this->assertDatabaseMissing('comments', [
        'author_name' => 'SpamBot',
    ]);

    Mail::assertNothingSent();
});

it('rate limits comments per IP', function () {
    Mail::fake();
    RateLimiter::clear('comment:127.0.0.1');
    $article = Article::factory()->create(['is_published' => true]);

    for ($i = 1; $i <= 3; $i++) {
        Livewire::test(ArticleComments::class, ['article' => $article])
            ->set('author_name', "User{$i}")
            ->set('content', "Comment {$i}")
            ->call('addComment')
            ->assertHasNoErrors();
    }

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->set('author_name', 'User4')
        ->set('content', 'Comment 4')
        ->call('addComment')
        ->assertHasErrors(['content']);

    $this->assertDatabaseCount('comments', 3);
});

it('sends a notification email when a comment is posted', function () {
    Mail::fake();
    $article = Article::factory()->create(['is_published' => true]);

    Livewire::test(ArticleComments::class, ['article' => $article])
        ->set('author_name', 'Pierre')
        ->set('content', 'Test notification')
        ->call('addComment');

    Mail::assertSent(NewCommentMail::class, function ($mail) {
        return $mail->hasTo(config('mail.contact_recipient'));
    });
});

it('deletes comments when article is deleted (cascade)', function () {
    $article = Article::factory()->create();
    Comment::factory()->count(3)->create(['article_id' => $article->id]);

    $this->assertDatabaseCount('comments', 3);

    $article->delete();

    $this->assertDatabaseCount('comments', 0);
});

it('renders the dashboard comment list for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.comments.index'))
        ->assertSuccessful();
});

it('can delete a comment from the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $comment = Comment::factory()->create();

    Livewire::test(CommentList::class)
        ->call('deleteComment', $comment->id)
        ->assertSuccessful();

    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

it('formats @mentions in formatted_content accessor', function () {
    $comment = Comment::factory()->create(['content' => 'Merci @Pierre pour cet article !']);

    expect($comment->formatted_content)
        ->toContain('<span class="text-primary font-semibold">@Pierre</span>')
        ->toContain('Merci')
        ->toContain('pour cet article !');
});

it('escapes HTML in formatted_content to prevent XSS', function () {
    $comment = Comment::factory()->create(['content' => '<script>alert("xss")</script> @User']);

    expect($comment->formatted_content)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;')
        ->toContain('<span class="text-primary font-semibold">@User</span>');
});

it('can mark all comments as read', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Comment::factory()->count(3)->create(['is_read' => false]);

    Livewire::test(CommentList::class)
        ->call('markAllAsRead');

    $this->assertEquals(0, Comment::where('is_read', false)->count());
});
