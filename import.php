<?php
include("./include/header.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['gerichtsname'] ?? '';
    $cuisine_id = (int)($_POST['cuisine'] ?? 0);
    $ernaehrungsweise_id = (int)($_POST['ernaehrungsweise'] ?? 0);
    $dauer = (int)($_POST['dauer'] ?? 0);
    $kalorien = (int)($_POST['kalorien'] ?? 0);
    $beschreibung = $_POST['beschreibung'] ?? '';
    $zubereitung = $_POST['zubereitung'] ?? '';

    
    // Validate required fields
    if (!empty($title) && !empty($cuisine_id) && !empty($dauer) && !empty($kalorien)) {
        try {
            // Insert into gericht table
            $query = "INSERT INTO gericht (title, cuisine_id, ernaehrungsweise_id, zubereitungszeit_min, kalorien, beschreibung, zubereitung) 
                      VALUES (:title, :cuisine_id, :ernaehrungsweise_id, :zubereitungszeit_min, :kalorien, :beschreibung, :zubereitung)";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':cuisine_id', $cuisine_id);
            $stmt->bindParam(':ernaehrungsweise_id', $ernaehrungsweise_id);
            $stmt->bindParam(':zubereitungszeit_min', $dauer);
            $stmt->bindParam(':kalorien', $kalorien);
            $stmt->bindParam(':beschreibung', $beschreibung);
            $stmt->bindParam(':zubereitung', $zubereitung); 
            
            if ($stmt->execute()) {
                $gericht_id = $conn->lastInsertId();
                
                // Handle ingredients if they exist
                if (!empty($_POST['lebensmittel']) && is_array($_POST['lebensmittel'])) {
                    $lebensmittel = $_POST['lebensmittel'];
                    $mengen = $_POST['menge'] ?? [];
                    $einheiten = $_POST['einheit'] ?? [];
                    
                    // Insert ingredients - Fixed SQL query
                    $ingredient_query = "INSERT INTO gericht_lebensmittel (gericht_id, lebensmittel_id, menge) 
                                       VALUES (:gericht_id, :lebensmittel_id, :menge)";
                    $ingredient_stmt = $conn->prepare($ingredient_query);
                    
                    for ($i = 0; $i < count($lebensmittel); $i++) {
                        if (!empty($lebensmittel[$i])) {
                            $ingredient_stmt->bindParam(':gericht_id', $gericht_id);
                            $ingredient_stmt->bindParam(':lebensmittel_id', $lebensmittel[$i]);
                           
                            $ingredient_stmt->execute();
                        }
                    }
                }
                
                $success_message = "Rezept wurde erfolgreich gespeichert!";
            } else {
                $error_message = "Fehler beim Speichern des Rezepts.";
            }
        } catch (PDOException $e) {
            $error_message = "Datenbankfehler: " . $e->getMessage();
        }
    } else {
        $error_message = "Bitte füllen Sie alle Pflichtfelder aus.";
    }
}

// Fetch cuisines from database
$cuisines = [];
try {
    $cuisine_query = "SELECT id, title FROM cuisine ORDER BY title";
    $cuisine_stmt = $conn->prepare($cuisine_query);
    $cuisine_stmt->execute();
    $cuisines = $cuisine_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Fehler beim Laden der Küchen: " . $e->getMessage();
}

// Fetch ernährungsweise from database
$ernaehrungsweisen = [];
try {
    $ernaehrung_query = "SELECT id, title FROM ernaehrungsweise ORDER BY title";
    $ernaehrung_stmt = $conn->prepare($ernaehrung_query);
    $ernaehrung_stmt->execute();
    $ernaehrungsweisen = $ernaehrung_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Fehler beim Laden der Ernährungsweisen: " . $e->getMessage();
}
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="card-title text-center text-primary mb-4 fw-bold">Neues Rezept hinzufügen</h1>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="row g-4">
                        <!-- Reihe 1: Grundinformationen -->
                        <div class="col-md-12">
                            <label for="gerichtsname" class="form-label fw-semibold">Gerichtsname *</label>
                            <input type="text" class="form-control" id="gerichtsname" name="gerichtsname" 
                                   placeholder="z.B. Omas Apfelkuchen" required 
                                   value="<?php echo htmlspecialchars($_POST['gerichtsname'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="cuisine" class="form-label fw-semibold">Cuisine *</label>
                            <select id="cuisine" name="cuisine" class="form-select" required>
                                <option value="">Bitte wählen...</option>
                                <?php foreach ($cuisines as $cuisine): ?>
                                    <option value="<?php echo $cuisine['id']; ?>" 
                                            <?php echo (isset($_POST['cuisine']) && $_POST['cuisine'] == $cuisine['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cuisine['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="ernaehrungsweise" class="form-label fw-semibold">Ernährungsweise</label>
                            <select id="ernaehrungsweise" name="ernaehrungsweise" class="form-select">
                                <option value="">Bitte wählen...</option>
                                <?php foreach ($ernaehrungsweisen as $ernaehrung): ?>
                                    <option value="<?php echo $ernaehrung['id']; ?>" 
                                            <?php echo (isset($_POST['ernaehrungsweise']) && $_POST['ernaehrungsweise'] == $ernaehrung['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ernaehrung['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Reihe 2: Zeit & Kalorien -->
                        <div class="col-md-6">
                            <label for="dauer" class="form-label fw-semibold">Dauer (in Minuten) *</label>
                            <input type="number" class="form-control" id="dauer" name="dauer" 
                                   placeholder="z.B. 45" required min="1"
                                   value="<?php echo htmlspecialchars($_POST['dauer'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="kalorien" class="form-label fw-semibold">Kalorien (pro Portion) *</label>
                            <input type="number" class="form-control" id="kalorien" name="kalorien" 
                                   placeholder="z.B. 550" required min="1"
                                   value="<?php echo htmlspecialchars($_POST['kalorien'] ?? ''); ?>">
                        </div>

                        <!-- Sektion 3: Zutaten -->
                        <div class="col-12">
                            <fieldset class="border p-3 rounded-3">
                                <legend class="fs-6 fw-semibold px-2">Zutaten</legend>
                                <div id="zutaten-liste">
                                    <!-- Erste Zutat-Zeile -->
                                    <div class="row g-2 mb-2 align-items-center">
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="lebensmittel[]" 
                                                   placeholder="Lebensmittel, z.B. Mehl">
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" name="menge[]" placeholder="Menge">
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-select" name="einheit[]">
                                                <option value="g">Gramm (g)</option>
                                                <option value="kg">Kilogramm (kg)</option>
                                                <option value="ml">Milliliter (ml)</option>
                                                <option value="l">Liter (l)</option>
                                                <option value="Stk.">Stück</option>
                                                <option value="EL">Esslöffel</option>
                                                <option value="TL">Teelöffel</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-zutat-btn" class="btn btn-sm btn-success mt-2">
                                    <i class="bi bi-plus-circle me-1"></i> Weitere Zutat
                                </button>
                            </fieldset>
                        </div>

                        <!-- Reihe 4: Beschreibung -->
                        <div class="col-12">
                            <label for="beschreibung" class="form-label fw-semibold">Beschreibung</label>
                            <textarea class="form-control" id="beschreibung" name="beschreibung" rows="3" 
                                      placeholder="Kurze Beschreibung des Gerichts..."><?php echo htmlspecialchars($_POST['beschreibung'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Reihe 5: Zubereitung -->
                        <div class="col-12">
                            <label for="zubereitung" class="form-label fw-semibold">Zubereitung</label>
                            <textarea class="form-control" id="zubereitung" name="zubereitung" rows="5" 
                                      placeholder="Beschreibe hier die Zubereitungsschritte..."><?php echo htmlspecialchars($_POST['zubereitung'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Reihe 6: Absenden-Button -->
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-gradient-primary w-100 p-3 fs-5 fw-bold rounded-3">
                                Rezept speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap 5 JS Bundle (via CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    // --- Dynamisches Hinzufügen von Zutatenfeldern ---
    const addZutatButton = document.getElementById('add-zutat-btn');
    const zutatenListe = document.getElementById('zutaten-liste');

    addZutatButton.addEventListener('click', function() {
        // Erstelle eine neue Zeile für die Zutat
        const neueZeile = document.createElement('div');
        neueZeile.className = 'row g-2 mb-2 align-items-center';

        // Füge die HTML-Struktur hinzu
        neueZeile.innerHTML = `
            <div class="col-sm-5">
                <input type="text" class="form-control" name="lebensmittel[]" placeholder="Lebensmittel">
            </div>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="menge[]" placeholder="Menge">
            </div>
            <div class="col-sm-4">
                <select class="form-select" name="einheit[]">
                    <option value="g">Gramm (g)</option>
                    <option value="kg">Kilogramm (kg)</option>
                    <option value="ml">Milliliter (ml)</option>
                    <option value="l">Liter (l)</option>
                    <option value="Stk.">Stück</option>
                    <option value="EL">Esslöffel</option>
                    <option value="TL">Teelöffel</option>
                </select>
            </div>
            <div class="col-sm-12 text-end">
                <button type="button" class="btn btn-sm btn-danger remove-zutat-btn">
                    <i class="bi bi-trash"></i> Entfernen
                </button>
            </div>
        `;

        // Hänge die neue Zeile an die Liste an
        zutatenListe.appendChild(neueZeile);
    });

    // Event delegation für das Entfernen von Zutaten
    zutatenListe.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-zutat-btn') || e.target.closest('.remove-zutat-btn')) {
            e.target.closest('.row').remove();
        }
    });
</script>

</body>
</html>