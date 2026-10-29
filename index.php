<?php
require_once 'db.php';

$currentYear = intval(date('Y'));
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;
$years = range($currentYear - 10, $currentYear + 10);

// Povlačenje iz baze samo onih meseci koji postoje za izabranu godinu
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE year = ?");
$stmt->execute([$selectedYear]);
$rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($rawRows as $row) {
    $data[$row['month']] = $row;
}

$months = [
    1 => 'Januar', 2 => 'Februar', 3 => 'Mart', 4 => 'April',
    5 => 'Maj', 6 => 'Jun', 7 => 'Jul', 8 => 'Avgust',
    9 => 'Septembar', 10 => 'Oktobar', 11 => 'Novembar', 12 => 'Decembar'
];
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Zapisnik Rezervacija - <?php echo $selectedYear; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-4 sm:p-6">

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><i class="fa-solid fa-calendar-days text-indigo-600 mr-2"></i> Zapisnik Rezervacija</h1>
                <p class="text-sm text-gray-500 mt-1">Prikaz 10 godina unazad i unapred (prikazuju se samo sačuvani meseci).</p>
            </div>
            
            <div class="mt-4 sm:mt-0 flex items-center gap-2">
                <label for="yearSelect" class="text-sm font-medium text-gray-700">Godina:</label>
                <select id="yearSelect" onchange="changeYear(this.value)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y === $selectedYear) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Tabela meseca -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                            <th class="p-4 font-semibold">Mesec</th>
                            <th class="p-4 font-semibold">Ime i prezime</th>
                            <th class="p-4 font-semibold">Datum rezervacije</th>
                            <th class="p-4 font-semibold text-center">Plaćeno</th>
                            <th class="p-4 font-semibold">Beleške</th>
                            <th class="p-4 font-semibold text-center">Akcija</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($months as $mNum => $mName): 
                            $row = $data[$mNum] ?? ['name' => '', 'reservation_date' => '', 'is_paid' => 0, 'note' => ''];
                        ?>
                        <tr class="hover:bg-gray-50/50 transition-colors" data-month="<?php echo $mNum; ?>">
                            <td class="p-4 font-medium text-gray-900"><?php echo $mName; ?></td>
                            <td class="p-4">
                                <input type="text" value="<?php echo htmlspecialchars($row['name']); ?>" placeholder="Unesite ime..." class="res-name w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="p-4">
                                <input type="date" value="<?php echo htmlspecialchars($row['reservation_date']); ?>" onchange="updateDateColor(this)" class="res-date border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            </td>
                            <td class="p-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="paid-checkbox sr-only peer" <?php echo ($row['is_paid'] == 1) ? 'checked' : ''; ?> onchange="confirmPaymentChange(this, <?php echo $selectedYear; ?>, <?php echo $mNum; ?>)">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 relative"></div>
                                </label>
                            </td>
                            <td class="p-4">
                                <textarea placeholder="Unesite beleške..." class="res-note w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none h-10"><?php echo htmlspecialchars($row['note']); ?></textarea>
                            </td>
                            <td class="p-4 text-center">
                                <button onclick="saveMonth(this, <?php echo $selectedYear; ?>, <?php echo $mNum; ?>)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs px-4 py-2 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5 mx-auto">
                                    <i class="fa-solid fa-floppy-disk"></i> Sačuvaj
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL ZA POTVRDU PLAĆANJA -->
    <div id="paymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Potvrda statusa plaćanja</h3>
            <p id="modalText" class="text-sm text-gray-600 mb-6">Da li ste sigurni da želite da promenite status?</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal(false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm rounded-lg">Otkaži</button>
                <button onclick="closeModal(true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg">Potvrdi</button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFIKACIJA -->
    <div id="toast" class="fixed bottom-5 right-5 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-2 z-50 text-sm">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <span id="toastMessage">Promena je sačuvana!</span>
    </div>

    <script>
        let pendingCheckbox = null;

        // Automatsko bojenje datuma pri učitavanju stranice
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.res-date').forEach(input => {
                updateDateColor(input);
            });
        });

        function updateDateColor(input) {
            const val = input.value;
            // Reset stilova
            input.style.backgroundColor = "";
            input.style.color = "";
            input.style.borderColor = "#e5e7eb";

            if (!val) return;

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const targetDate = new Date(val);
            targetDate.setHours(0, 0, 0, 0);

            const diffDays = Math.ceil((targetDate - today) / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                // Prošlo -> Crna pozadina, beli tekst
                input.style.backgroundColor = "#111827";
                input.style.color = "#ffffff";
                input.style.borderColor = "#111827";
            } else if (diffDays <= 5) {
                // Danas ili u narednih 5 dana -> Crvena
                input.style.backgroundColor = "#fee2e2";
                input.style.color = "#991b1b";
                input.style.borderColor = "#f87171";
            } else if (diffDays <= 14) {
                // Do dve nedelje (6-14 dana) -> Žuta
                input.style.backgroundColor = "#fef3c7";
                input.style.color = "#92400e";
                input.style.borderColor = "#fbbf24";
            } else if (diffDays <= 30) {
                // Do mesec dana -> Zelena
                input.style.backgroundColor = "#d1fae5";
                input.style.color = "#065f46";
                input.style.borderColor = "#34d399";
            } else if (diffDays <= 100) {
                // Do 100 dana -> Plava
                input.style.backgroundColor = "#dbeafe";
                input.style.color = "#1e40af";
                input.style.borderColor = "#60a5fa";
            } else {
                // Više od 100 dana -> Bela
                input.style.backgroundColor = "#ffffff";
                input.style.color = "#1f2937";
            }
        }

        function changeYear(year) {
            window.location.href = `?year=${year}`;
        }

        function confirmPaymentChange(checkbox, year, month) {
            pendingCheckbox = checkbox;
            const isChecked = checkbox.checked;
            document.getElementById('modalText').innerText = isChecked ? 
                "Da li ste sigurni da je ova rezervacija PLAĆENA?" : 
                "Da li ste sigurni da poništite plaćanje ove rezervacije?";
            
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('paymentModal').classList.add('flex');
        }

        function closeModal(confirmed) {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (confirmed && pendingCheckbox) {
                const row = pendingCheckbox.closest('tr');
                const month = row.getAttribute('data-month');
                const year = document.getElementById('yearSelect').value;
                const is_paid = pendingCheckbox.checked ? 1 : 0;

                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'update_payment', year, month, is_paid })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("Status plaćanja uspešno promenjen u bazi!");
                    } else {
                        alert("Došlo je do greške.");
                        pendingCheckbox.checked = !pendingCheckbox.checked;
                    }
                });
            } else if (pendingCheckbox) {
                pendingCheckbox.checked = !pendingCheckbox.checked;
            }
            pendingIndex = null;
        }

        function saveMonth(button, year, month) {
            const row = button.closest('tr');
            const name = row.querySelector('.res-name').value;
            const reservation_date = row.querySelector('.res-date').value;
            const note = row.querySelector('.res-note').value;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_month', year, month, name, reservation_date, note })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                } else {
                    alert("Greška pri čuvanju.");
                }
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').innerText = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }
    </script>
</body>
</html>
