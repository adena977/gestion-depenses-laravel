<nav class="navbar bg-base-100 shadow-sm border-b border-base-300">
    <div class="flex-1">
        <!-- Mobile menu button -->
        <label for="sidebar-drawer" class="btn btn-ghost drawer-button lg:hidden">
            <i class="fas fa-bars"></i>
        </label>
        
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs ml-4">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $crumb)
                        <li>{{ $crumb }}</li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
    
    <div class="flex-none gap-2">
        <!-- Notifications -->
        <div class="dropdown dropdown-end">
            <button class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <i class="fas fa-bell"></i>
                    <span class="badge badge-xs badge-primary indicator-item"></span>
                </div>
            </button>
            <div class="dropdown-content bg-base-100 rounded-box z-[1] w-80 p-2 shadow">
                <div class="p-4">
                    <h3 class="font-bold">Notifications</h3>
                    <div class="mt-2 space-y-2">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Budget nourriture presque dépassé</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Theme Toggle -->
        <label class="swap swap-rotate btn btn-ghost btn-circle">
            <input type="checkbox" class="theme-controller" value="expenseTheme" />
            <i class="fas fa-sun swap-on"></i>
            <i class="fas fa-moon swap-off"></i>
        </label>
        
        <!-- User Menu -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full bg-primary text-primary-content">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                <li>
                    <a href="{{ route('profile.edit') }}" class="justify-between">
                        Profil
                        <span class="badge">Nouveau</span>
                    </a>
                </li>
                <li><a>Paramètres</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left">Déconnexion</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>