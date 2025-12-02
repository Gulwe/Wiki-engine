<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<h1>🔍 Wiki Engine Diagnostic</h1>";
echo "<style>body{background:#1a1a2e;color:#eee;font-family:monospace;padding:20px;line-height:1.6}h2{color:#e94560;margin-top:30px}pre{background:#16213e;padding:15px;border-left:3px solid #e94560;overflow:auto;margin:10px 0}.good{color:#2ecc71}.bad{color:#e74c3c}.warn{color:#f39c12}</style>";

// Test 1: Database
echo "<h2>1️⃣ Database Connection</h2>";
try {
    require_once __DIR__ . '/../core/Database.php';
    $db = Database::getInstance()->getConnection();
    echo "<span class='good'>✅ Connected</span><br>";
    
    // MySQL: pokaż nazwę bazy i tabele
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    echo "Database: <strong>$dbName</strong><br>";
    
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>Tables: " . implode(', ', $tables) . "</pre>";
} catch (Exception $e) {
    echo "<span class='bad'>❌ ERROR: " . $e->getMessage() . "</span><br>";
    exit;
}

// Test 2: Which settings table exists?
echo "<h2>2️⃣ Settings Table Check</h2>";
$tableExists = false;
$tableName = '';

try {
    $count = $db->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
    echo "<span class='good'>✅ Table 'site_settings' exists with $count rows</span><br>";
    $tableExists = true;
    $tableName = 'site_settings';
} catch (Exception $e) {
    echo "<span class='warn'>⚠️ Table 'site_settings': " . $e->getMessage() . "</span><br>";
}

try {
    $count = $db->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    echo "<span class='good'>✅ Table 'settings' exists with $count rows</span><br>";
    if (!$tableExists) {
        $tableExists = true;
        $tableName = 'settings';
    }
} catch (Exception $e) {
    echo "<span class='warn'>⚠️ Table 'settings': " . $e->getMessage() . "</span><br>";
}

if (!$tableExists) {
    echo "<span class='bad'>💥 PROBLEM: No settings table found!</span><br>";
    echo "<p>Dostępne tabele: " . implode(', ', $tables) . "</p>";
    exit;
}

// Test 3: Settings Model
echo "<h2>3️⃣ Settings Model</h2>";
try {
    require_once __DIR__ . '/../models/Settings.php';
    $settings = new Settings();
    echo "<span class='good'>✅ Model loaded</span><br>";
    
    $all = $settings->getAll();
    echo "Found " . count($all) . " settings<br>";
    
    if (count($all) > 0) {
        echo "<pre>";
        foreach (array_slice($all, 0, 10) as $s) {
            echo htmlspecialchars($s['setting_key']) . " = " . htmlspecialchars(substr($s['setting_value'], 0, 50)) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<span class='warn'>⚠️ No settings found!</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='bad'>❌ ERROR: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 4: ThemeLoader
echo "<h2>4️⃣ ThemeLoader</h2>";
try {
    require_once __DIR__ . '/../core/ThemeLoader.php';
    
    $siteName = ThemeLoader::get('site_name', 'NOT_FOUND');
    echo "site_name = <strong>" . htmlspecialchars($siteName) . "</strong><br>";
    
    $primaryColor = ThemeLoader::get('primary_color', 'NOT_FOUND');
    echo "primary_color = <strong style='color:$primaryColor'>$primaryColor</strong><br>";
    
    $css = ThemeLoader::generateCSS();
    echo "<span class='good'>✅ CSS generated: " . strlen($css) . " chars</span><br>";
    
    echo "<details><summary>Show CSS</summary><pre>" . htmlspecialchars($css) . "</pre></details>";
} catch (Exception $e) {
    echo "<span class='bad'>❌ ERROR: " . $e->getMessage() . "</span><br>";
}

// Test 5: CSS File
echo "<h2>5️⃣ CSS File</h2>";

// Spróbuj różne ścieżki
$paths = [
    __DIR__ . '/css/style.css',
    __DIR__ . '/../public/css/style.css',
    $_SERVER['DOCUMENT_ROOT'] . '/css/style.css',
    'D:/xampp/htdocs/wiki-engine/public/css/style.css'
];

echo "<strong>Testing paths:</strong><br>";
foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "<span class='good'>✅ FOUND: $path (" . number_format(filesize($path)) . " bytes)</span><br>";
    } else {
        echo "<span class='bad'>❌ NOT FOUND: $path</span><br>";
    }
}

// Test 6: Write test
echo "<h2>6️⃣ Write Test</h2>";
try {
    $testKey = 'test_' . time();
    $testValue = 'value_' . time();
    
    $result = $settings->set($testKey, $testValue);
    echo "Write: " . ($result ? "<span class='good'>✅ OK</span>" : "<span class='bad'>❌ FAIL</span>") . "<br>";
    
    $readback = $settings->get($testKey, 'NOT_FOUND');
    echo "Readback: <strong>$readback</strong><br>";
    
    if ($readback === $testValue) {
        echo "<span class='good'>✅ READ/WRITE WORKING!</span><br>";
    } else {
        echo "<span class='bad'>❌ MISMATCH!</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='bad'>❌ ERROR: " . $e->getMessage() . "</span><br>";
}

echo "<hr><a href='/' style='padding:10px;background:#eee;color:#000;text-decoration:none;border-radius:5px'>🏠 Home</a> ";
echo "<a href='/admin/customize' style='padding:10px;background:#eee;color:#000;text-decoration:none;border-radius:5px;margin-left:10px'>🎨 Customize</a>";
?>
