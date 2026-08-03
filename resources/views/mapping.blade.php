<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetricsDash Enterprise - Configuration & Mappage BDD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6 md:p-10">

    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Header -->
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="flex size-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h1 class="text-2xl font-black text-white">MetricsDash SDK - Configuration & Mappage BDD</h1>
                </div>
                <p class="text-sm text-slate-400 mt-1">
                    Sélectionnez vos tables et colonnes réelles (<span class="text-emerald-400 font-semibold">Paiements, Packs, Utilisateurs</span>) pour alimenter dynamiquement vos tableaux de bord.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="triggerSync()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all">
                    <span>⚡ Synchroniser les Ventes Vers MetricsDash</span>
                </button>
            </div>
        </div>

        <!-- Alert Notification Box -->
        <div id="alertBox" class="hidden p-4 rounded-xl text-xs font-semibold"></div>

        <!-- Form Grid -->
        <form id="mappingForm" onsubmit="saveMapping(event)" class="space-y-6">

            <!-- Section 1 : Table des Paiements / Commandes -->
            <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-sm">💳</span>
                        1. Table des Paiements & Transactions
                    </h2>
                    <span class="text-xs text-slate-400">Requis</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Table des Paiements</label>
                        <select id="payTable" onchange="loadColumns('payTable', ['payAmount', 'payCurr', 'payStatus', 'payDate', 'payUserKey', 'payPackKey'])" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none">
                            <option value="">Sélectionner une table...</option>
                            @foreach($tables as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Montant (€)</label>
                        <select id="payAmount" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Devise</label>
                        <select id="payCurr" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Statut (ex: status)</label>
                        <select id="payStatus" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Valeur de Statut Valide (ex: paid)</label>
                        <input type="text" id="payValidStatus" value="paid" placeholder="paid, completed" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Date de Vente</label>
                        <select id="payDate" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Clé Utilisateur (foreign key user_id)</label>
                        <select id="payUserKey" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Clé Package (foreign key pack_id)</label>
                        <select id="payPackKey" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-emerald-500 outline-none"></select>
                    </div>
                </div>
            </div>

            <!-- Section 2 : Table des Packages / Offres -->
            <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20 text-sm">📦</span>
                        2. Table des Packages & Formules (Packs)
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Table des Packages</label>
                        <select id="packTable" onchange="loadColumns('packTable', ['packPrimaryId', 'packName', 'packPrice'])" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-purple-500 outline-none">
                            <option value="">Sélectionner une table...</option>
                            @foreach($tables as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Clé Primaire (ex: id)</label>
                        <select id="packPrimaryId" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-purple-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Nom du Pack (ex: name)</label>
                        <select id="packName" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-purple-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Prix du Pack (ex: price)</label>
                        <select id="packPrice" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-purple-500 outline-none"></select>
                    </div>
                </div>
            </div>

            <!-- Section 3 : Table des Utilisateurs / Acheteurs -->
            <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-sm">👤</span>
                        3. Table des Utilisateurs & Clients (Acheteurs)
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Table des Utilisateurs</label>
                        <select id="userTable" onchange="loadColumns('userTable', ['userPrimaryId', 'userName', 'userEmail'])" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-cyan-500 outline-none">
                            <option value="">Sélectionner une table...</option>
                            @foreach($tables as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Clé Primaire (ex: id)</label>
                        <select id="userPrimaryId" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-cyan-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne Nom (ex: name)</label>
                        <select id="userName" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-cyan-500 outline-none"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Colonne E-mail (ex: email)</label>
                        <select id="userEmail" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-cyan-500 outline-none"></select>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all">
                    Sauvegarder & Prévisualiser l'Extraction
                </button>
            </div>
        </form>

        <!-- Preview Table -->
        <div id="previewContainer" class="hidden p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4">
            <h3 class="text-base font-bold text-white">Aperçu des 3 Dernières Transactions Mappées</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950 text-slate-400">
                        <tr>
                            <th class="p-3">ID Commande</th>
                            <th class="p-3">Acheteur</th>
                            <th class="p-3">E-mail</th>
                            <th class="p-3">Pack Souscrit</th>
                            <th class="p-3">Montant</th>
                            <th class="p-3">Date</th>
                        </tr>
                    </thead>
                    <tbody id="previewBody" class="divide-y divide-slate-800 text-slate-200"></tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        async function loadColumns(tableSelectId, targetSelectIds) {
            const tableName = document.getElementById(tableSelectId).value;
            if (!tableName) return;

            try {
                const res = await fetch(`/metrics-dash/api/columns?table=${tableName}`);
                const data = await res.json();
                
                targetSelectIds.forEach(selectId => {
                    const el = document.getElementById(selectId);
                    if (!el) return;
                    el.innerHTML = '<option value="">Sélectionner une colonne...</option>';
                    data.columns.forEach(col => {
                        const option = document.createElement('option');
                        option.value = col;
                        option.textContent = col;
                        el.appendChild(option);
                    });
                });
            } catch (e) {
                console.error('Erreur de chargement des colonnes:', e);
            }
        }

        async function saveMapping(e) {
            e.preventDefault();
            const payload = {
                payments: {
                    table: document.getElementById('payTable').value,
                    amount: document.getElementById('payAmount').value,
                    currency: document.getElementById('payCurr').value,
                    status: document.getElementById('payStatus').value,
                    valid_status_value: document.getElementById('payValidStatus').value,
                    created_at: document.getElementById('payDate').value,
                    foreign_user_id: document.getElementById('payUserKey').value,
                    foreign_pack_id: document.getElementById('payPackKey').value,
                },
                packs: {
                    table: document.getElementById('packTable').value,
                    primary_id: document.getElementById('packPrimaryId').value,
                    name: document.getElementById('packName').value,
                    price: document.getElementById('packPrice').value,
                },
                users: {
                    table: document.getElementById('userTable').value,
                    primary_id: document.getElementById('userPrimaryId').value,
                    name: document.getElementById('userName').value,
                    email: document.getElementById('userEmail').value,
                }
            };

            const res = await fetch('/metrics-dash/api/mapping', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            const alertBox = document.getElementById('alertBox');
            alertBox.classList.remove('hidden', 'bg-red-500/20', 'bg-emerald-500/20', 'text-red-400', 'text-emerald-400');

            if (res.ok) {
                alertBox.classList.add('bg-emerald-500/20', 'text-emerald-400');
                alertBox.textContent = `✅ ${data.message} (${data.preview_count} transactions identifiées)`;
                
                // Render sample
                const previewContainer = document.getElementById('previewContainer');
                const previewBody = document.getElementById('previewBody');
                previewBody.innerHTML = '';
                (data.sample || []).forEach(row => {
                    previewBody.innerHTML += `
                        <tr>
                            <td class="p-3 font-mono text-emerald-400">${row.orderId}</td>
                            <td class="p-3 font-bold">${row.userName}</td>
                            <td class="p-3 text-slate-400">${row.userEmail}</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 font-semibold">${row.pack}</span></td>
                            <td class="p-3 font-black text-white">${row.amount} ${row.currency}</td>
                            <td class="p-3 text-slate-400">${row.date}</td>
                        </tr>
                    `;
                });
                previewContainer.classList.remove('hidden');
            } else {
                alertBox.classList.add('bg-red-500/20', 'text-red-400');
                alertBox.textContent = `❌ Erreur : Veuillez vérifier votre sélection.`;
            }
        }

        async function triggerSync() {
            const res = await fetch('/metrics-dash/api/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await res.json();
            alert(`⚡ Synchronisation terminée ! ${data.result?.synced_count || 0} transactions synchronisées vers MetricsDash.`);
        }
    </script>
</body>
</html>
