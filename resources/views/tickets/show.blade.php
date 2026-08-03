<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Chamado #{{ $ticket->code }}
            </h2>
            <a href="{{ route('tickets.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                &larr; Voltar para a lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ALERTA DE SUCESSO -->
            @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('status') }}
            </div>
            @endif

            <!-- CARD 1: DETALHES DO CHAMADO -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4 flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $ticket->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Aberto por <span
                                class="font-semibold text-gray-700 dark:text-gray-300">{{ $ticket->client->name }}</span>
                            em {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 ml-2">
                            {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                </div>

                <div class="prose dark:prose-invert max-w-none mb-6">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Descrição</h4>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $ticket->description }}</p>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <strong>Técnico Responsável:</strong>
                        {{ $ticket->technician ? $ticket->technician->name : 'Não atribuído' }}
                    </div>
                </div>
            </div>

            <!-- CARD 2: FORMULÁRIO DE GESTÃO (NOVO BLOCO ENTRA AQUI) -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Gerenciar Chamado</h4>

                <form action="{{ route('tickets.update', $ticket) }}" method="POST"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    @method('PUT')

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Aberto</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Em
                                Andamento</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolvido
                            </option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Fechado</option>
                        </select>
                    </div>

                    <!-- Técnico Responsável -->
                    <div>
                        <x-input-label for="technician_id" value="Técnico Responsável" />
                        <select id="technician_id" name="technician_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Selecionar Técnico --</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $ticket->technician_id == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botão Atualizar -->
                    <div>
                        <x-primary-button>Atualizar Chamado</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- CARD 3: SEÇÃO DE COMENTÁRIOS E HISTÓRICO -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Histórico & Interações</h4>

                <!-- Novo Comentário -->
                <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" class="mb-6 space-y-3">
                    @csrf
                    <div>
                        <textarea name="content" rows="3"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                            placeholder="Digite uma mensagem ou atualização..." required></textarea>
                    </div>
                    <x-primary-button>Enviar Mensagem</x-primary-button>
                </form>

                <hr class="border-gray-200 dark:border-gray-700 my-4">

                <!-- Lista de Comentários -->
                <div class="space-y-4">
                    @forelse($ticket->comments as $comment)
                    <div
                        class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-sm text-gray-800 dark:text-gray-200">
                                {{ $comment->user->name }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $comment->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $comment->content }}
                        </p>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">Nenhuma mensagem registrada até o momento.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>