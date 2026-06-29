{{-- Nome --}}
<div>
    <label for="nome" class="block text-sm font-medium mb-2 text-foco-muted">Nome</label>
    <input type="text" id="nome" name="nome" maxlength="60"
           value="{{ old('nome', $category->nome ?? '') }}"
           placeholder="Ex: Alimentação, Transporte..."
           class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors"
           autofocus>
</div>

{{-- Tipo --}}
<div>
    <label for="tipo" class="block text-sm font-medium mb-2 text-foco-muted">Tipo</label>
    <select id="tipo" name="tipo"
            class="w-full bg-foco-surface border border-foco-border rounded-xl px-4 py-3 text-foco-text focus:outline-none focus:border-foco-accent transition-colors">
        <option value="saida"  {{ old('tipo', $category->tipo ?? 'saida') === 'saida'  ? 'selected' : '' }}>Saída</option>
        <option value="entrada"{{ old('tipo', $category->tipo ?? '') === 'entrada' ? 'selected' : '' }}>Entrada</option>
        <option value="ambos"  {{ old('tipo', $category->tipo ?? '') === 'ambos'   ? 'selected' : '' }}>Ambos</option>
    </select>
</div>

{{-- Cor --}}
<div>
    <label for="cor" class="block text-sm font-medium mb-2 text-foco-muted">Cor</label>
    <div class="flex items-center gap-3">
        <input type="color" id="cor" name="cor"
               value="{{ old('cor', $category->cor ?? '#6366F1') }}"
               class="w-14 h-14 rounded-xl border border-foco-border bg-foco-surface cursor-pointer p-1">
        <span class="text-foco-muted text-sm">Clique para escolher a cor</span>
    </div>
</div>

{{-- Ícone --}}
<div x-data="{ icone: '{{ old('icone', $category->icone ?? 'tag') }}' }">
    <label class="block text-sm font-medium mb-2 text-foco-muted">Ícone</label>
    <input type="hidden" name="icone" :value="icone">
    <div class="grid grid-cols-6 gap-2">
        @foreach(['tag','utensils','car','heart-pulse','gamepad-2','briefcase','home','shopping-cart','zap','music','coffee','book','plane','dumbbell','shirt','baby','graduation-cap','wrench','smartphone','gift'] as $ic)
        <button type="button" @click="icone = '{{ $ic }}'"
                :class="icone === '{{ $ic }}' ? 'border-foco-accent bg-foco-accent/10' : 'border-foco-border hover:border-foco-accent/50'"
                class="p-3 border-2 rounded-xl flex items-center justify-center transition-colors">
            <i data-lucide="{{ $ic }}" class="w-5 h-5 text-foco-text"></i>
        </button>
        @endforeach
    </div>
</div>
