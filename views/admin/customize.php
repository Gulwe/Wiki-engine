<?php
$allSettings = $settings->getAll();

// Grupuj według kategorii
$grouped = [];
foreach ($allSettings as $setting) {
    $grouped[$setting['category']][] = $setting;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(ThemeLoader::get('site_name', 'Wiki Engine')) ?> - Customizacja</title>
    <link rel="stylesheet" href="/css/style.css">
    <?= ThemeLoader::generateCSS() ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        <div class="customize-header">
            <h1>🎨 Customizacja Strony</h1>
            <div class="customize-actions">
                <a href="/admin" class="btn">← Panel Admina</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✅</span>
                <div class="alert-content">
                    <strong>Sukces!</strong>
                    <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">🚫</span>
                <div class="alert-content">
                    <strong>Błąd!</strong>
                    <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" action="/admin/customize/save" class="customize-form">
            <div class="customize-tabs">
                <button type="button" class="tab-btn active" data-tab="general">⚙️ Ogólne</button>
                <button type="button" class="tab-btn" data-tab="design">🎨 Wygląd</button>
                <button type="button" class="tab-btn" data-tab="features">✨ Funkcje</button>
            </div>
            
            <?php foreach ($grouped as $category => $categorySettings): ?>
                <div class="tab-content" id="tab-<?= $category ?>" style="<?= $category !== 'general' ? 'display: none;' : '' ?>">
                    <h2>
                        <?php if ($category === 'general'): ?>⚙️ Ogólne Ustawienia<?php endif; ?>
                        <?php if ($category === 'design'): ?>🎨 Wygląd i Kolory<?php endif; ?>
                        <?php if ($category === 'features'): ?>✨ Funkcje<?php endif; ?>
                    </h2>
                    
                    <div class="settings-grid">
                        <?php foreach ($categorySettings as $setting): ?>
                            <div class="setting-item">
                                <label for="<?= $setting['setting_key'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $setting['setting_key'])) ?>
                                </label>
                                
                                <?php if ($setting['setting_type'] === 'text'): ?>
                                    <input 
                                        type="text" 
                                        id="<?= $setting['setting_key'] ?>"
                                        name="setting_<?= $setting['setting_key'] ?>"
                                        value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                        class="form-control"
                                    >
                                
                                <?php elseif ($setting['setting_type'] === 'textarea'): ?>
                                    <textarea 
                                        id="<?= $setting['setting_key'] ?>"
                                        name="setting_<?= $setting['setting_key'] ?>"
                                        rows="6"
                                        class="form-control"
                                    ><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                
                                <?php elseif ($setting['setting_type'] === 'color'): ?>
                                    <div class="color-picker-wrapper">
                                        <input 
                                            type="color" 
                                            id="<?= $setting['setting_key'] ?>"
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                            class="color-picker"
                                        >
                                        <input 
                                            type="text" 
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                            class="color-value"
                                            readonly
                                        >
                                    </div>
                                
                                <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                                    <label class="toggle-switch">
                                        <input 
                                            type="checkbox" 
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            value="1"
                                            <?= $setting['setting_value'] == '1' ? 'checked' : '' ?>
                                        >
                                        <span class="toggle-slider"></span>
                                    </label>
                                
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input 
                                        type="number" 
                                        id="<?= $setting['setting_key'] ?>"
                                        name="setting_<?= $setting['setting_key'] ?>"
                                        value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                        class="form-control"
                                    >
                                
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz Zmiany</button>
                <button type="button" class="btn btn-secondary" onclick="location.reload()">🔄 Anuluj</button>
            </div>
        </form>
    </div>

<script>
// Zakładki
$('.tab-btn').on('click', function() {
    const tab = $(this).data('tab');
    
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    
    $('.tab-content').hide();
    $('#tab-' + tab).show();
});

// Color picker
$('.color-picker').on('input', function() {
    const value = $(this).val();
    $(this).next('.color-value').val(value);

    // LIVE PREVIEW: primary/secondary/background/header
    const id = $(this).attr('id');

    const root = document.documentElement;
    if (id === 'primary_color') {
        root.style.setProperty('--primary-color', value);
    }
    if (id === 'secondary_color') {
        root.style.setProperty('--secondary-color', value);
    }
    if (id === 'background_color') {
        root.style.setProperty('--background-color', value);
        document.body.style.background =
            `linear-gradient(135deg, ${value} 0%, #1a0033 100%)`;
    }
    if (id === 'header_color') {
        // dla modern-header
        document.querySelectorAll('.modern-header').forEach(el => {
            el.style.backgroundColor = value;
        });
    }
});

// Boolean / feature toggles – np. maintenance_mode podgląd ikony/alertu
$('input[type="checkbox"][name^="setting_"]').on('change', function() {
    const key = this.name.replace('setting_', '');
    const checked = this.checked;

    if (key === 'maintenance_mode') {
        // Możesz np. pokazać alert na górze strony jako preview
        let bar = document.getElementById('maintenance-preview-bar');
        if (!bar && checked) {
            bar = document.createElement('div');
            bar.id = 'maintenance-preview-bar';
            bar.textContent = 'Tryb konserwacji będzie WŁĄCZONY dla zwykłych użytkowników.';
            bar.style.cssText = 'position:fixed;top:0;left:0;right:0;padding:10px 20px;' +
                'background:rgba(251,191,36,0.15);border-bottom:1px solid #fbbf24;' +
                'color:#fde68a;font-size:14px;z-index:2000;text-align:center;';
            document.body.appendChild(bar);
        } else if (bar && !checked) {
            bar.remove();
        }
    }
});

// Ostrzeżenie przed opuszczeniem
let formChanged = false;
$('.customize-form input, .customize-form textarea, .customize-form select').on('change input', function() {
    formChanged = true;
});

$(window).on('beforeunload', function() {
    if (formChanged) {
        return 'Masz niezapisane zmiany.';
    }
});

$('.customize-form').on('submit', function() {
    formChanged = false;
});
</script>

    
</body>
</html>
