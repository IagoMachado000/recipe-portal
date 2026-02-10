@extends('layouts.app')

@section('content')
<section class="container">
    <h1 class="text-capitalize fs-1 text-body-secondary">
        nova receita
    </h1>

    <p class="fs-3 text-body-tertiary">Preencha os detalhes abaixo para registrar a receita.</p>

    <form action="{{ route('recipes.store') }}" method="post" novalidate class="row g-4">
        @csrf

        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
            </div>
            <input
                type="text"
                name="title"
                id="title"
                class="form-control @error('title') is-invalid @enderror"
                value="{{ old('title') }}"
                required
            >
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <label for="description" class="form-label">Descrição</label>
            </div>
            <textarea
                name="description"
                id="description"
                cols="30"
                rows="10"
                class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label">Ingredients <span class="text-danger">*</span></label>
            <div id="ingredients-list" class="d-flex flex-column gap-2">
                @foreach (old('ingredients', ['']) as $i => $ingredient)
                    <div class="ingredient-item d-flex align-items-center gap-2">
                        <input
                            type="text"
                            name="ingredients[]"
                            class="form-control @error('ingredients.'.$i) is-invalid @enderror"
                            value="{{ $ingredient }}"
                            required
                        >

                        <button
                            type="button"
                            class="btn btn-outline-danger btn-remove-ingredient d-none"
                            aria-label="Remove ingredient"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                            </svg>
                        </button>
                    </div>

                    @error('ingredients.'.$i)
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                @endforeach
            </div>

            <button
                type="button"
                id="add-ingredient"
                class="btn btn-outline-success btn-sm mt-3"
            >
                + novo ingrediente
            </button>
        </div>

        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <label for="steps" class="form-label">Passo a Passo</label>
            </div>
            <textarea
                name="steps"
                id="steps"
                cols="30"
                rows="10"
                class="form-control @error('steps') is-invalid @enderror"
                required>{{ old('steps') }}</textarea>
            <small class="text-muted fs-6">Obs: 1 passo por linha</small>
            @error('steps')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex gap-3">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('recipes.dashboard') }}" type="submit" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('ingredients-list');
    const addBtn = document.getElementById('add-ingredient');

    function updateRemoveButtons() {
        const items = list.querySelectorAll('.ingredient-item');
        const show = items.length > 1;

        items.forEach(item => {
            const btn = item.querySelector('.btn-remove-ingredient');
            btn.classList.toggle('d-none', !show);
        });
    }

    addBtn.addEventListener('click', () => {
        const item = document.createElement('div');
        item.className = 'ingredient-item d-flex align-items-center gap-2';

        item.innerHTML = `
            <input
                type="text"
                name="ingredients[]"
                class="form-control"
                required
            >
            <button
                type="button"
                class="btn btn-outline-danger btn-remove-ingredient"
                aria-label="Remove ingredient"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                </svg>
            </button>
        `;

        list.appendChild(item);
        updateRemoveButtons();
    });

    list.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remove-ingredient')) {
            e.target.closest('.ingredient-item').remove();
            updateRemoveButtons();
        }
    });

    // Estado inicial (importante para old())
    updateRemoveButtons();
});
</script>

@endsection
