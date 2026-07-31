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
            
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4 flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $ticket->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Aberto por <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $ticket->client->name }}</span> em {{ $ticket->created_at->format('d/m/Y H:i') }}
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

        </div>
    </div>
</x-app-layout>