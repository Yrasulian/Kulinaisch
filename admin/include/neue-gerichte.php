
<?php
include("./include/header.php");    
?>
      

<main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="card-title text-center text-primary mb-4 fw-bold">Neues Rezept hinzufügen</h1>
                        
                        <form class="row g-4">
                            <!-- Reihe 1: Grundinformationen -->
                            <div class="col-md-12">
                                <label for="gerichtsname" class="form-label fw-semibold">Gerichtsname</label>
                                <input type="text" class="form-control" id="gerichtsname" placeholder="z.B. Omas Apfelkuchen">
                            </div>
                            <div class="col-md-6">
                                <label for="cuisine" class="form-label fw-semibold">Cuisine</label>
                                <select id="cuisine" class="form-select">
                                    <option selected>Bitte wählen...</option>
                                    <option value="1">Deutsche Küche</option>
                                    <option value="2">Italienische Küche</option>
                                    <option value="3">Asiatische Küche</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ernaehrungsweise" class="form-label fw-semibold">Ernährungsweise</label>
                                <select id="ernaehrungsweise" class="form-select">
                                    <option selected>Bitte wählen...</option>
                                    <option value="1">Vegetarisch</option>
                                    <option value="2">Vegan</option>
                                    <option value="3">Alles</option>
                                </select>
                            </div>

                            <!-- Reihe 2: Zeit & Kalorien -->
                            <div class="col-md-6">
                                <label for="dauer" class="form-label fw-semibold">Dauer (in Minuten)</label>
                                <input type="number" class="form-control" id="dauer" placeholder="z.B. 45">
                            </div>
                            <div class="col-md-6">
                                <label for="kalorien" class="form-label fw-semibold">Kalorien (pro Portion)</label>
                                <input type="number" class="form-control" id="kalorien" placeholder="z.B. 550">
                            </div>

                            <!-- Sektion 3: Zutaten (komplett überarbeitet) -->
                            <div class="col-12">
                                <fieldset class="border p-3 rounded-3">
                                    <legend class="fs-6 fw-semibold px-2">Zutaten</legend>
                                    <div id="zutaten-liste">
                                        <!-- Dies ist die Vorlage für eine Zutat-Zeile -->
                                        <div class="row g-2 mb-2 align-items-center">
                                            <div class="col-sm-5">
                                                <input type="text" class="form-control" name="lebensmittel[]" placeholder="Lebensmittel, z.B. Mehl">
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
                                <label for="beschreibung" class="form-label fw-semibold">Beschreibung / Zubereitung</label>
                                <textarea class="form-control" id="beschreibung" rows="5" placeholder="Beschreibe hier die Zubereitungsschritte..."></textarea>
                            </div>
                            
                            <!-- Reihe 5: Absenden-Button -->
                            <div class="col-12 mt-5">
                                <button type="submit" class="btn btn-gradient-primary w-100 p-3 fs-5 fw-bold rounded-3">Rezept speichern</button>
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
        // --- Dynamisches Hinzufügen von Zutatenfeldern (angepasst für Bootstrap) ---
        const addZutatButton = document.getElementById('add-zutat-btn');
        const zutatenListe = document.getElementById('zutaten-liste');

        addZutatButton.addEventListener('click', function() {
            // Erstelle eine neue Zeile für die Zutat
            const neueZeile = document.createElement('div');
            // WICHTIG: Hier werden die Bootstrap Grid-Klassen verwendet
            neueZeile.className = 'row g-2 mb-2 align-items-center';

            // Füge die HTML-Struktur mit den korrekten Bootstrap-Klassen hinzu
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
            `;

            // Hänge die neue Zeile an die Liste an
            zutatenListe.appendChild(neueZeile);
        });
    </script>
</body>
</html>

    