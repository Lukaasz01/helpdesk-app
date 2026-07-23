<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Abrir Novo Chamado
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('tickets.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="title" value="Título do Problema" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="priority" value="Prioridade" />
                        <select id="priority" name="priority" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baixa</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Média</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                        </select>
                        <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descrição Detalhada" />
                        <textarea id="description" name="description" rows="5" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Enviar Chamado</x-primary-button>
                        <a href="{{ route('tickets.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>