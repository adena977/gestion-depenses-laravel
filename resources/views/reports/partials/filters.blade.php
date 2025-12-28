<div class="card bg-base-100 shadow-lg mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.monthly') }}" class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Année</span>
                </label>
                <select name="year" class="select select-bordered">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Mois</span>
                </label>
                <select name="month" class="select select-bordered">
                    @foreach([
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                    ] as $key => $value)
                        <option value="{{ $key }}" {{ ($month ?? date('m')) == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex space-x-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                <a href="{{ route('reports.monthly') }}" class="btn btn-outline">
                    <i class="fas fa-redo mr-2"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>