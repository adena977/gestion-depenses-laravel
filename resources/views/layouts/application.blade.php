<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Tableau de Bord') - {{ config('app.name', 'Gestion de Dépenses') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Drawer Layout avec DaisyUI -->
    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
        
        <!-- Contenu principal -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            <nav class="navbar bg-base-100 shadow-sm sticky top-0 z-40">
                <div class="flex-none lg:hidden">
                    <label for="sidebar-drawer" class="btn btn-square btn-ghost drawer-button">
                        <i class="fas fa-bars text-xl"></i>
                    </label>
                </div>
                <div class="flex-1 px-4">
                    @if (isset($header))
                        {{ $header }}
                    @else
                        <h1 class="text-xl font-bold text-primary">@yield('page-title', 'Tableau de Bord')</h1>
                    @endif
                </div>
                <div class="flex-none gap-2">
                    <!-- Theme Toggle -->
                    <label class="swap swap-rotate btn btn-ghost btn-circle">
                        <input type="checkbox" class="theme-controller" value="dark" />
                        <i class="fas fa-sun swap-on text-xl"></i>
                        <i class="fas fa-moon swap-off text-xl"></i>
                    </label>
                    
                    <!-- Notifications (simplifié) -->
                    <div class="dropdown dropdown-end">
                        <button class="btn btn-ghost btn-circle">
                            <i class="fas fa-bell text-lg"></i>
                        </button>
                        <div tabindex="0" class="mt-3 z-[1] p-2 shadow-lg menu menu-sm dropdown-content bg-base-100 rounded-box w-80">
                            <div class="px-4 py-2 border-b border-base-300">
                                <h4 class="font-bold">Notifications</h4>
                            </div>
                            <div class="px-4 py-6 text-center">
                                <i class="fas fa-bell-slash text-3xl text-base-content/30 mb-2"></i>
                                <p class="text-sm text-base-content/70">Aucune notification</p>
                                <p class="text-xs text-base-content/50 mt-1">Le système de notifications n'est pas activé</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-lg">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </div>
                        <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
                            <li>
                                <a href="{{ route('dashboard') }}" class="justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        Tableau de bord
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('profile.edit') }}" class="justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-cog mr-2"></i>
                                        Mon Profil
                                    </div>
                                </a>
                            </li>
                            <li><hr class="my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-error/10 text-error">
                                        <div class="flex items-center">
                                            <i class="fas fa-sign-out-alt mr-2"></i>
                                            Déconnexion
                                        </div>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Messages Flash -->
            <div class="container mx-auto px-4 pt-4">
                @if(session('success'))
                    <div class="alert alert-success shadow-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-xl mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button class="btn btn-sm btn-ghost" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error shadow-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-xl mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button class="btn btn-sm btn-ghost" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning shadow-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-xl mr-2"></i>
                            <span>{{ session('warning') }}</span>
                        </div>
                        <button class="btn btn-sm btn-ghost" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info shadow-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-xl mr-2"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button class="btn btn-sm btn-ghost" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <!-- Erreurs de validation -->
                @if($errors->any())
                    <div class="alert alert-error shadow-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-xl mr-2"></i>
                            <div>
                                <p class="font-bold">Des erreurs sont survenues :</p>
                                <ul class="list-disc list-inside text-sm mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-ghost" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Contenu de la page -->
            <main class="flex-1 p-4 md:p-6">
                {{ $slot }}
            </main>

            <!-- Footer -->
           <footer class="footer footer-center p-4 bg-base-200 text-base-content border-t border-base-300">
    <div>
        <p>© {{ date('Y') }} Gestion de Dépenses - Tous droits réservés</p>
        <p class="text-sm mt-1">Suivez vos finances efficacement</p>
    </div>
</footer>
        </div>

        <!-- Sidebar -->
        <div class="drawer-side z-50">
            <label for="sidebar-drawer" class="drawer-overlay"></label>
            <aside class="bg-base-100 w-80 p-4 min-h-screen flex flex-col">
                <!-- Logo -->
                <div class="px-4 py-6 border-b border-base-300">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center">
                            <i class="fas fa-wallet text-xl text-primary-content"></i>
                        </div>
                       <div>
    <h1 class="text-xl font-bold text-primary">Gestion de Dépenses</h1>
    <p class="text-xs text-base-content/70">Gérez vos finances intelligemment</p>
</div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="px-4 py-6 border-b border-base-300">
                    <div class="flex items-center space-x-3">
                        <div class="avatar placeholder">
                           <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-gradient-to-r from-primary to-secondary text-primary-content flex items-center justify-center">
    <span class="text-sm md:text-lg font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold truncate">{{ auth()->user()->name }}</h3>
                            <p class="text-sm text-base-content/70 truncate">{{ auth()->user()->email }}</p>
                            <div class="flex items-center mt-1">
                                <span class="badge badge-sm badge-success">Actif</span>
                                <span class="text-xs text-base-content/50 ml-2">
                                    Membre depuis {{ auth()->user()->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto py-4">
                    <ul class="menu space-y-1">
                        <li class="menu-title">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/50">Navigation Principale</span>
                        </li>
                        
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="{{ request()->routeIs('dashboard') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-chart-line w-5 text-center"></i>
                                <span>Tableau de Bord</span>
                                @if(request()->routeIs('dashboard'))
                                    <span class="ml-auto">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </span>
                                @endif
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('transactions.index') }}" 
                               class="{{ request()->routeIs('transactions.*') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-exchange-alt w-5 text-center"></i>
                                <span>Transactions</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('categories.index') }}" 
                               class="{{ request()->routeIs('categories.*') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-tags w-5 text-center"></i>
                                <span>Catégories</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('budgets.index') }}" 
                               class="{{ request()->routeIs('budgets.*') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-chart-pie w-5 text-center"></i>
                                <span>Budgets</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('reports.index') }}" 
                               class="{{ request()->routeIs('reports.*') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-chart-bar w-5 text-center"></i>
                                <span>Rapports</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('savings.index') }}" 
                               class="{{ request()->routeIs('savings.*') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-piggy-bank w-5 text-center"></i>
                                <span>Épargne</span>
                            </a>
                        </li>

                        <li class="menu-title mt-6 pt-6 border-t border-base-300">
                            <span class="text-xs font-semibold uppercase tracking-wider text-base-content/50">Paramètres</span>
                        </li>
                        
                        <li>
                            <a href="{{ route('profile.edit') }}" 
                               class="{{ request()->routeIs('profile.*') ? 'active bg-primary/10 text-primary' : '' }}
                                      flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-user-cog w-5 text-center"></i>
                                <span>Mon Profil</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="#" 
                               class="flex items-center space-x-3 py-3 px-4 rounded-lg hover:bg-base-200 transition-colors">
                                <i class="fas fa-cog w-5 text-center"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Quick Stats -->
                <div class="mt-auto pt-6 border-t border-base-300">
                    <div class="stats shadow bg-base-200 w-full">
                        <div class="stat">
                            <div class="stat-title">Solde du mois</div>
                            @php
                                $monthIncome = auth()->user()->transactions()
                                    ->where('type', 'income')
                                    ->whereMonth('date', now()->month)
                                    ->sum('amount') ?? 0;
                                $monthExpense = auth()->user()->transactions()
                                    ->where('type', 'expense')
                                    ->whereMonth('date', now()->month)
                                    ->sum('amount') ?? 0;
                                $monthBalance = $monthIncome - $monthExpense;
                            @endphp
                            <div class="stat-value text-lg {{ $monthBalance >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($monthBalance, 0, ',', ' ') }} FDJ
                            </div>
                            <div class="stat-desc">
                                {{ now()->translatedFormat('F Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Sidebar -->
                <div class="mt-4 pt-4 border-t border-base-300">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-error w-full">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Déconnexion
                        </button>
                    </form>
                    <div class="text-center text-xs text-base-content/50 mt-3">
                        <p>v1.0.0 • {{ date('Y') }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Theme toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser le thème
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            
            // Mettre à jour le toggle
            const themeToggle = document.querySelector('.theme-controller');
            if (themeToggle) {
                themeToggle.checked = theme === 'dark';
                themeToggle.addEventListener('change', function() {
                    const newTheme = this.checked ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                });
            }
            
            // Auto-close alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    if (!alert.classList.contains('alert-permanent')) {
                        alert.remove();
                    }
                });
            }, 5000);
            
            // Close sidebar on mobile when clicking outside
            document.querySelector('.drawer-overlay')?.addEventListener('click', () => {
                document.getElementById('sidebar-drawer').checked = false;
            });
        });
        
        // Active menu highlighting
        function setActiveMenu() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.menu a').forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.startsWith(href) && href !== '/') {
                    link.classList.add('active');
                }
            });
        }
        
        // Call on page load
        document.addEventListener('DOMContentLoaded', setActiveMenu);
    </script>

    @stack('scripts')
</body>
</html>