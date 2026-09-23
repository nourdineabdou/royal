@extends('layouts.production')

@section('title', 'Détails du Repas')
@section('page_title', $meal->name)
@section('page_subtitle', $meal->category->name ?? 'Sans catégorie')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Image Section -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden sticky top-8">
                    <div class="h-80 bg-gray-200 flex items-center justify-center">
                        @if($meal->image)
                            <img src="{{ asset('storage/' . $meal->image) }}" alt="{{ $meal->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex flex-col items-center text-gray-400">
                                <i class="fas fa-image text-6xl mb-4"></i>
                                <p class="text-lg">Pas d'image</p>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 space-y-3">
                        @can('meals.edit')
                        <a href="{{ route('meals.edit', $meal) }}" class="w-full block text-center bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">
                            <i class="fas fa-edit mr-2"></i>Éditer
                        </a>
                        <a href="{{ route('meals.recipe.edit', $meal) }}" class="w-full block text-center bg-emerald-600 text-white py-3 rounded-lg hover:bg-emerald-700 transition duration-300 font-medium">
                            <i class="fas fa-list-ul mr-2"></i>Recette
                        </a>
                        @endcan
                        @can('meals.delete')
                        <form action="{{ route('meals.destroy', $meal) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce repas ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition duration-300 font-medium">
                                <i class="fas fa-trash mr-2"></i>Supprimer
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Details Section -->
            <div class="md:col-span-2">
                <!-- Title Card -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <h1 class="text-4xl font-bold text-gray-800 mb-4">{{ $meal->name }}</h1>

                    <!-- Category Badge -->
                    <div class="inline-block px-4 py-2 bg-blue-100 text-blue-800 text-sm rounded-full font-semibold mb-6">
                        <i class="fas fa-layer-group mr-2"></i>{{ $meal->category->name ?? 'Sans catégorie' }}
                    </div>

                    <!-- Price Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-gray-600 text-sm mb-2">Prix</p>
                        <p class="text-5xl font-bold text-green-600">{{ number_format($meal->price, 2) }} MRU</p>
                    </div>
                </div>

                <!-- Accompaniments Card -->
                @if($meal->accompaniments->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-star text-yellow-500 mr-3"></i>Accompagnements Disponibles
                        </h2>
                        <div class="space-y-4">
                            @foreach($meal->accompaniments as $accompaniment)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $accompaniment->name }}</p>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-tag mr-1"></i>
                                            @if($accompaniment->type === 'single')
                                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">Choix unique</span>
                                            @else
                                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-medium">Choix multiple</span>
                                            @endif
                                        </p>
                                    </div>
                                    <p class="text-2xl font-bold text-green-600">{{ number_format($accompaniment->price, 2) }} MRU</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-8 mb-6">
                        <p class="text-center text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>Aucun accompagnement associé à ce repas
                        </p>
                    </div>
                @endif

                <!-- Recipe Card -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-list-ul text-emerald-500 mr-3"></i>Recette
                        </h2>
                        @can('meals.edit')
                        <a href="{{ route('meals.recipe.edit', $meal) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-800">
                            <i class="fas fa-edit mr-1"></i>Modifier
                        </a>
                        @endcan
                    </div>
                    @if($meal->recipe && $meal->recipe->items->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($meal->recipe->items as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <span class="font-semibold text-gray-800">{{ $item->product->name ?? 'Produit supprimé' }}</span>
                                    <span class="text-gray-600">{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }} {{ $item->product->unit->name ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-blue-800 bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                            <i class="fas fa-info-circle mr-2"></i>Aucune recette définie pour ce plat — les besoins en produits ne peuvent pas être calculés automatiquement.
                        </p>
                    @endif
                </div>

                <!-- Info Section -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-info-circle text-indigo-500 mr-3"></i>Informations
                    </h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Créé le</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $meal->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Dernière modification</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $meal->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Catégorie</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $meal->category->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Accompagnements</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $meal->accompaniments->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
</div>
@endsection
