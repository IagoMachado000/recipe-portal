@extends('layouts.app')
@section('content')
<div class="container py-5">
    <!-- Recipe Header -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <h1 class="h2 fw-light mb-3">{{ $recipe->title }}</h1>
            @if($recipe->description)
                <p class="text-muted">{{ $recipe->description }}</p>
            @endif
        </div>
        <div class="col-lg-4 text-end">
            <div class="mb-3">
                <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="font-size: 1.2rem;">
                            @if($i <= round($recipe->rating_avg))
                                ★
                            @else
                                ☆
                            @endif
                        </span>
                    @endfor
                </div>
                <small class="text-muted">
                    {{ number_format($recipe->rating_avg, 1) }}
                    ({{ $recipe->ratings_count }} avaliações)
                </small>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
            </div>
        </div>
    </div>
    <!-- Content Tabs -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Ingredients -->
            <section class="mb-5">
                <h3 class="h5 fw-light text-muted mb-3">Ingredientes</h3>
                <div class="bg-light p-4 rounded">
                    <ul class="list-unstyled mb-0">
                        @foreach ($recipe->ingredients as $ingredient)
                            <li class="mb-2">• {{ $ingredient }}</li>
                        @endforeach
                    </ul>
                </div>
            </section>
            <!-- Steps -->
            <section>
                <h3 class="h5 fw-light text-muted mb-3">Modo de Preparo</h3>
                <div class="bg-light p-4 rounded">
                    @foreach ($recipe->steps as $index => $step)
                        <div class="mb-3">
                            {{ $step }}
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
        <!-- Sidebar: Rating & Comments -->
        <div class="col-lg-4">
            <!-- Rating Section -->
            <section class="mb-4">
                <h3 class="h5 fw-light text-muted mb-3">Avaliar Receita</h3>
                @auth
                    @if(!Auth::user()->ratings()->where('recipe_id', $recipe->id)->exists())
                        <form action="{{ route('ratings.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="recipe_id" value="{{ $recipe->id }}">
                            <div class="mb-3">
                                <div class="btn-group w-100" role="group">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="submit" name="score" value="{{ $i }}"
                                                class="btn btn-outline-warning">★</button>
                                    @endfor
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <small>Você já avaliou esta receita</small>
                        </div>
                    @endif
                @else
                    <div class="alert alert-light">
                        <small>
                            <a href="{{ route('login') }}">Faça login</a> para avaliar
                        </small>
                    </div>
                @endauth
            </section>
            <!-- Comments Section -->
            <section>
                <h3 class="h5 fw-light text-muted mb-3">
                    Comentários ({{ $recipe->comments->count() }})
                </h3>

                @auth
                    <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                        @csrf
                        <input type="hidden" name="recipe_id" value="{{ $recipe->id }}">
                        <textarea name="body" class="form-control" rows="3"
                                  placeholder="Deixe seu comentário..." required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm mt-2">Comentar</button>
                    </form>
                @else
                    <div class="alert alert-light mb-4">
                        <small>
                            <a href="{{ route('login') }}">Faça login</a> para comentar
                        </small>
                    </div>
                @endauth
                <!-- Comments List -->
                <div class="comments-list">
                    @foreach ($recipe->comments()->with('user')->orderBy('created_at', 'desc')->get() as $comment)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <strong class="small">{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-0 small">{{ $comment->body }}</p>
                            @auth
                                @if($comment->user_id === Auth::id())
                                    <form action="{{ route('comments.destroy', $comment) }}"
                                          method="POST" class="d-inline mt-2">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                                            Excluir
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
