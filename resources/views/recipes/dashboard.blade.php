@extends('layouts.app')
@section('content')
<div class="container py-5">
    <!-- Header com Actions -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h3 fw-light text-muted">Minhas Receitas</h1>
            <p class="text-muted small">Gerencie suas receitas compartilhadas</p>
        </div>
        @if ($recipes->count())
            <a href="{{ route('recipes.create') }}" class="btn btn-primary">
                + Nova Receita
            </a>
        @endif
    </div>
    <!-- Grid Cards com Actions -->
    <div class="row g-4">
        @forelse($recipes as $recipe)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-light mb-3">
                            <a href="{{ route('recipes.show', $recipe) }}"
                               class="text-decoration-none text-dark">
                                {{ Str::limit($recipe->title, 45) }}
                            </a>
                        </h5>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                {{ $recipe->created_at->format('d/m/Y') }}
                            </small>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2">
                                    {{ $recipe->comments_count }}
                                </span>
                                <span class="badge bg-warning text-dark">
                                    ★ {{ number_format($recipe->rating_avg, 1) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-3">
                        <div class="d-flex justify-content-end gap-2 w-100" role="group">
                            <a href="{{ route('recipes.show', $recipe) }}"
                               class="btn btn-outline-secondary btn-sm">Ver</a>
                            <a href="{{ route('recipes.edit', $recipe) }}"
                               class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="{{ route('recipes.destroy', $recipe) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Tem certeza?')">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h4 class="fw-light text-muted mb-3">Você ainda não criou receitas</h4>
                <a href="{{ route('recipes.create') }}" class="btn btn-primary">
                    Criar Primeira Receita
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
