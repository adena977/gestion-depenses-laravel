<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .info-table .label {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 30%;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .data-table th {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .data-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .expense {
            color: #e74c3c;
        }
        
        .income {
            color: #27ae60;
        }
        
        .totals {
            margin-top: 30px;
            border-top: 2px solid #333;
            padding-top: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="subtitle">Généré le {{ $generated_at->format('d/m/Y à H:i') }}</div>
    </div>
    
    <!-- Informations générales -->
    <table class="info-table">
        <tr>
            <td class="label">Période</td>
            <td>{{ $period === 'month' ? 'Mensuel' : ($period === 'year' ? 'Annuel' : 'Toutes périodes') }}</td>
            <td class="label">Nombre de transactions</td>
            <td>{{ $transactions->count() }}</td>
        </tr>
    </table>
    
    <!-- Tableau des transactions -->
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Description</th>
                <th>Montant (FDJ)</th>
                <th>Méthode de paiement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="{{ $transaction->type === 'expense' ? 'expense' : 'income' }}">
                            {{ $transaction->type === 'expense' ? 'Dépense' : 'Revenu' }}
                        </span>
                    </td>
                    <td>{{ $transaction->category->name }}</td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                    <td class="{{ $transaction->type === 'expense' ? 'expense' : 'income' }}">
                        {{ number_format($transaction->amount, 2, ',', ' ') }}
                    </td>
                    <td>
                        @switch($transaction->payment_method)
                            @case('cash')
                                Espèces
                                @break
                            @case('card')
                                Carte
                                @break
                            @case('transfer')
                                Virement
                                @break
                            @case('mobile_money')
                                Mobile Money
                                @break
                            @default
                                {{ $transaction->payment_method }}
                        @endswitch
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totaux -->
    <div class="totals">
        <div class="total-row">
            <span>Total Dépenses:</span>
            <span class="expense">{{ number_format($totals['expenses'] ?? 0, 2, ',', ' ') }} FDJ</span>
        </div>
        <div class="total-row">
            <span>Total Revenus:</span>
            <span class="income">{{ number_format($totals['incomes'] ?? 0, 2, ',', ' ') }} FDJ</span>
        </div>
        <div class="total-row" style="font-weight: bold; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 10px;">
            <span>Balance:</span>
            <span style="color: {{ ($totals['balance'] ?? 0) >= 0 ? '#27ae60' : '#e74c3c' }}">
                {{ number_format($totals['balance'] ?? 0, 2, ',', ' ') }} FDJ
            </span>
        </div>
    </div>
    
    <!-- Pied de page -->
    <div class="footer">
        <p>Document généré par l'application de Gestion de Dépenses</p>
        <p>© {{ date('Y') }} - Tous droits réservés</p>
    </div>
</body>
</html>