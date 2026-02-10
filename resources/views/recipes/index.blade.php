@extends('layouts.app')

@push('styles')
    <style>
        .sort-icon {
            transition: all 0.3s ease;
        }
        .sort-icon::before {
            content: "↓";
        }
        .sort-icon.sort-asc::before {
            content: "↑";
        }
        .sort-icon.sort-desc::before {
            content: "↓";
        }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="text-center mb-5">
        <h1 class="h2 fw-light text-muted">Receitas Deliciosas</h1>
        <p class="text-muted">Descubra novas receitas compartilhadas por nossa comunidade</p>
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                Faça login
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                Registre-se
            </a>
        @endguest
    </div>
    <div class="row g-4">
        <div class="col-12">
            <div class="row justify-content-between">
                <div class="col-lg-6">
                    <form action="{{ route('recipes.index') }}" method="GET" class="mb-4">
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <div class="flex-grow-1">
                                <input type="text" name="search"
                                    class="form-control"
                                    placeholder="Buscar receitas..."
                                    value="{{ request('search') }}">
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary w-100">
                                    🔍 Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 col-xl-4">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <div class="d-flex gap-2 flex-grow-1">
                            <select name="filter" id="filter" class="form-select">
                                <option value="" disabled selected>Selecione um filtro</option>
                                <option value="date" {{ request('filter') == 'date' ? 'selected' : '' }}>Data de criação</option>
                                <option value="title" {{ request('filter') == 'title' ? 'selected' : '' }}>Nome da receita</option>
                                <option value="rating" {{ request('filter') == 'rating' ? 'selected' : '' }}>Avaliações</option>
                            </select>
                            <button id="filter_order" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                                <i class="sort-icon"></i>
                            </button>
                        </div>
                        <a href="{{ route('recipes.index')}}" class="btn btn-outline-secondary text-capitalize">limpar filtros</a>
                    </div>
                </div>
            </div>
        </div>
        @forelse($recipes as $recipe)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-0">
                        <a href="{{ route('recipes.show', $recipe->slug) }}" class="d-block p-4 text-decoration-none text-dark">
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
    <div class="d-flex justify-content-center mt-5">
        {{ $recipes->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('filter');
            const sortButton = document.getElementById('filter_order');

            // Evento de mudança de filtro
            filterSelect.addEventListener('change', function(e) {
                const filterType = e.target.value;
                if (!filterType) return;

                const url = new URL(window.location);
                const params = new URLSearchParams(url.search);

                // Limpa parâmetros antigos
                params.delete('filter');
                params.delete('sort_by');
                params.delete('sort_order');

                // Define ordenação padrão para cada filtro
                const sortMapping = {
                    'date': { sort_by: 'created_at', sort_order: 'desc' },
                    'title': { sort_by: 'title', sort_order: 'asc' },
                    'rating': { sort_by: 'rating_avg', sort_order: 'desc' }
                };

                const filterConfig = sortMapping[filterType] || sortMapping.date;

                // Aplica novo filtro com ordenação padrão
                params.set('filter', filterType);
                params.set('sort_by', filterConfig.sort_by);
                params.set('sort_order', filterConfig.sort_order);

                window.location.href = `${url.pathname}?${params.toString()}`;
            });

            // Evento de mudança de ordenação
            sortButton.addEventListener('click', function(e) {
                e.preventDefault();

                const url = new URL(window.location);
                const params = new URLSearchParams(url.search);

                const currentOrder = params.get('sort_order') || 'desc';
                const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';

                // Manter o filtro atual
                const currentFilter = params.get('filter');
                if (currentFilter) {
                    params.set('filter', currentFilter);
                }

                // Define sort_by baseado no filtro atual
                const sortByMapping = {
                    'date': 'created_at',
                    'title': 'title',
                    'rating': 'rating_avg'
                };

                const sortBy = sortByMapping[currentFilter] || 'created_at';

                params.set('sort_by', sortBy);
                params.set('sort_order', newOrder);

                // Atualiza ícone visual
                updateSortIcon(newOrder);

                window.location.href = `${url.pathname}?${params.toString()}`;
            });

            // Função para atualizar ícone
            function updateSortIcon(order) {
                const icon = document.querySelector('#filter_order i');
                icon.className = `sort-icon sort-${order}`;
            }

            // Inicializa ícone na página load
            const initialOrder = new URLSearchParams(window.location.search).get('sort_order') || 'desc';
            updateSortIcon(initialOrder);
        });
    </script>
@endpush
@endsection
