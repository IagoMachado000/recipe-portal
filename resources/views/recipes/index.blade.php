@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Header Clean -->
    <div class="text-center mb-5">
        <h1 class="h2 fw-light text-muted">Receitas Deliciosas</h1>
        <p class="text-muted">Descubra novas receitas compartilhadas por nossa comunidade</p>
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                Faça login para interagir
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                Registra-se
            </a>
        @endguest
    </div>
    <!-- Grid de Cards Clean -->
    <div class="row g-4">
        @forelse($recipes as $recipe)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-0">
                        <a href="{{ route('recipes.show', $recipe) }}" class="d-block p-4 text-decoration-none text-dark">
                            <h5 class="card-title fw-light mb-3">
                                {{ Str::limit($recipe->title, 45) }}
                            </h5>

                            @if($recipe->description)
                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($recipe->description, 80) }}
                                </p>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2">
                                        <small>
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= round($recipe->rating_avg))
                                                    ★
                                                @else
                                                    ☆
                                                @endif
                                            @endfor
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        {{ $recipe->rating_avg }} ({{ $recipe->rating_count }})
                                    </small>
                                </div>
                                <small class="text-muted">
                                    {{ $recipe->comments_count }} comentários
                                </small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">Nenhuma receita publicada ainda.</p>
            </div>
        @endforelse
    </div>
    <!-- Pagination Clean -->
    <div class="d-flex justify-content-center mt-5">
        {{ $recipes->links() }}
    </div>
</div>
@endsection
