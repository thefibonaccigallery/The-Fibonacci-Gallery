---
name: laravel
description: General best practices for writing and structuring Laravel 12 applications, including Eloquent models/relationships, controllers, Form Request validation, API Resources, migrations, routing, service/action classes, queues/jobs, policies & authorization, and testing conventions. Use this skill whenever writing, reviewing, or refactoring PHP code in a Laravel project — controllers, models, migrations, routes, requests, resources, jobs, policies, middleware, or tests. Trigger even if the user doesn't say "Laravel" explicitly but is clearly working in a Laravel project structure (e.g. files under app/Http/Controllers, app/Models, database/migrations, routes/web.php or api.php) or asking about PHP backend architecture, validation, authorization, or database design in that context.
---

# Laravel 12 Best Practices

Guidance for writing idiomatic, maintainable Laravel 12 code. Laravel 12 keeps
the streamlined application structure introduced in Laravel 11 (minimal
`app/Http/Kernel.php`, slim `bootstrap/app.php`) — don't reintroduce Laravel 9/10
era boilerplate (separate Kernel classes, default middleware groups defined in
Kernel) unless the project specifically still targets an older version.

## 1. Application structure (Laravel 11/12 style)

- **Bootstrap, not Kernel.** Middleware, exception handling, and routing
  registration live in `bootstrap/app.php`, not `app/Http/Kernel.php` or
  `app/Exceptions/Handler.php`. Don't create those files unless the project is
  explicitly on an older Laravel version.
  ```php
  // bootstrap/app.php
  return Application::configure(basePath: dirname(__DIR__))
      ->withRouting(
          web: __DIR__.'/../routes/web.php',
          api: __DIR__.'/../routes/api.php',
          commands: __DIR__.'/../routes/console.php',
          health: '/up',
      )
      ->withMiddleware(function (Middleware $middleware) {
          $middleware->alias(['admin' => \App\Http\Middleware\EnsureUserIsAdmin::class]);
      })
      ->withExceptions(function (Exceptions $exceptions) {
          //
      })->create();
  ```
- Most framework service providers are consolidated; avoid creating new
  providers for things that fit naturally in `AppServiceProvider::boot()`
  unless there's a clear reason to separate concerns (e.g. a large domain
  module).

## 2. Controllers — thin, single-responsibility

Controllers orchestrate; they don't contain business logic. Prefer
**single-action (invokable) controllers** for complex, non-CRUD operations, and
resource controllers for standard CRUD.

```php
// Standard CRUD
class PostController extends Controller
{
    public function index(Request $request) { /* ... */ }
    public function store(StorePostRequest $request) { /* ... */ }
    public function show(Post $post) { /* ... */ }
    public function update(UpdatePostRequest $request, Post $post) { /* ... */ }
    public function destroy(Post $post) { /* ... */ }
}

// Single, non-CRUD action
class PublishPostController extends Controller
{
    public function __invoke(Post $post)
    {
        $post->publish();
        return redirect()->back();
    }
}
```

Keep validation, authorization, and heavy business logic **out** of the
controller body — delegate to Form Requests, Policies, and
Action/Service classes (see §4, §6).

## 3. Always use Form Requests for validation

Don't validate inline in the controller with `$request->validate([...])` beyond
trivial one-off cases. Extract a Form Request — it keeps controllers clean and
co-locates authorization with validation.

```php
class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
```
```php
public function store(StorePostRequest $request)
{
    $post = Post::create($request->validated());
    return new PostResource($post);
}
```
Use rule objects/enums for complex or reused validation logic rather than
inlining repeated rule arrays across multiple requests.

## 4. Eloquent — models stay focused, query logic stays out of controllers

- **Relationships**: type-hint return types; keep relationship methods simple.
  ```php
  class Post extends Model
  {
      public function author(): BelongsTo
      {
          return $this->belongsTo(User::class, 'user_id');
      }

      public function comments(): HasMany
      {
          return $this->hasMany(Comment::class);
      }
  }
  ```
- **Avoid N+1 queries** — eager load relationships used in loops/views:
  ```php
  $posts = Post::with(['author', 'comments'])->paginate(20);
  ```
- **Use query scopes** for reusable, named query fragments instead of repeating
  `where()` chains across the codebase:
  ```php
  public function scopePublished(Builder $query): void
  {
      $query->whereNotNull('published_at')->where('published_at', '<=', now());
  }
  // Post::published()->get();
  ```
- **Mass assignment** — use `$fillable` explicitly (prefer it over `$guarded =
  []`) so it's clear what's intentionally writable from user input.
- **Casts** — use the `casts()` method (Laravel 11+ convention) rather than the
  `$casts` property for new models:
  ```php
  protected function casts(): array
  {
      return [
          'published_at' => 'datetime',
          'metadata' => 'array',
          'is_featured' => 'boolean',
      ];
  }
  ```
- Don't put complex business logic (e.g. multi-step state transitions, calls to
  external services) inside model methods — pull that into Action/Service
  classes (§6) that take the model as a dependency.

## 5. API Resources for response shaping

Never return Eloquent models directly from API endpoints. Use API Resources to
control exactly what's serialized — this prevents accidentally leaking columns
and decouples your DB schema from your API contract.

```php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => Str::limit($this->body, 150),
            'author' => new UserResource($this->whenLoaded('author')),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
```
Use `whenLoaded()` for relationships to avoid triggering lazy loads, and
`ResourceCollection` (or `Resource::collection()`) for lists.

## 6. Action / Service classes for business logic

For logic that's more than a one-liner — especially anything reused across
multiple controllers, jobs, or commands — extract a dedicated class rather than
letting it live in a controller or model. A common, simple convention is a
single-purpose "Action" class with an `execute()` or `handle()` method:

```php
class PublishPostAction
{
    public function execute(Post $post): Post
    {
        $post->update(['published_at' => now()]);
        PostPublished::dispatch($post);
        return $post;
    }
}
```
```php
public function __invoke(Post $post, PublishPostAction $action)
{
    $action->execute($post);
    return redirect()->back();
}
```
This keeps controllers thin, makes business logic independently testable, and
avoids "fat models" that accumulate unrelated responsibilities. Don't
over-engineer trivial CRUD with this pattern — reserve it for logic with real
complexity or reuse.

## 7. Authorization — Policies, not inline checks

Define a Policy per model rather than scattering `if ($user->id !==
$post->user_id)` checks through controllers.

```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```
```php
public function update(UpdatePostRequest $request, Post $post)
{
    // already enforced in UpdatePostRequest::authorize(), or explicitly:
    $this->authorize('update', $post);
    // ...
}
```
For role/permission-based authorization (e.g. via Spatie Laravel Permission),
keep role/permission checks inside Policies or Gate definitions — not scattered
through controllers and Blade/Vue conditionals as raw string checks.

## 8. Migrations — explicit, reversible, descriptive

- Name migrations descriptively: `create_posts_table`,
  `add_published_at_to_posts_table`.
- Always implement `down()` correctly, even though rollback is used less in
  CI/CD-driven deploys — it matters for local dev.
- Use foreign key constraints with explicit `onDelete()` behavior:
  ```php
  $table->foreignId('user_id')->constrained()->cascadeOnDelete();
  ```
- Avoid editing old, already-deployed migrations — add a new migration instead.
  Editing historical migrations breaks environments that already ran them.

## 9. Routing

- Group related routes with shared prefixes/middleware rather than repeating
  middleware per-route:
  ```php
  Route::middleware(['auth:sanctum'])->prefix('api/v1')->group(function () {
      Route::apiResource('posts', PostController::class);
  });
  ```
- Prefer `Route::apiResource()` / `Route::resource()` over manually declaring
  each CRUD route, unless the resource genuinely doesn't fit the convention.
- Use route model binding (type-hinted model in the controller method) instead
  of manually querying by ID inside the method.

## 10. Jobs & queues for anything slow or external

Don't perform slow operations (emails, external API calls, image processing,
report generation) synchronously inside the request lifecycle. Dispatch a job.

```php
class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function handle(): void
    {
        Mail::to($this->user)->send(new WelcomeMail($this->user));
    }
}
// SendWelcomeEmail::dispatch($user);
```
Set sensible `$tries`/`$backoff` for jobs that call external/flaky services, and
handle failures explicitly (`failed()` method) rather than letting them fail
silently.

## 11. Testing

- Prefer **Feature tests** (HTTP-level, hitting routes) for most coverage; use
  **Unit tests** for isolated logic in Action/Service classes with no framework
  dependencies.
- Use model factories for test data, not manual `Model::create([...])` with
  hardcoded fields scattered across tests:
  ```php
  Post::factory()->published()->for($user, 'author')->create();
  ```
- Use `RefreshDatabase` (or `DatabaseTransactions` for speed when applicable)
  to keep tests isolated.
- Test authorization explicitly (e.g. assert a non-owner gets a 403), not just
  the happy path.

## 12. Common review flags

- Business logic inline in a controller method beyond a few lines → extract to
  an Action/Service class (§6).
- `$request->validate([...])` inline for anything beyond a trivial 1-2 field
  request → extract a Form Request (§3).
- Eloquent models returned directly from API endpoints → wrap in a Resource (§5).
- Missing eager loading causing N+1s in loops/views (§4).
- Authorization logic duplicated across controllers instead of a Policy (§7).
- `app/Http/Kernel.php` or `app/Exceptions/Handler.php` present in what's
  meant to be a fresh Laravel 11/12 project → flag as legacy structure (§1).
- Synchronous external API calls or email sending inside a request-handling
  controller method → should likely be a queued job (§10).