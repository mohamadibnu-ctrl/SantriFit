<?php
$pdo = new PDO('mysql:host=localhost;dbname=santrifit_db', 'root', '');
$cols = $pdo->query('DESCRIBE santri')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | Null:' . $c['Null'] . ' | Default:' . var_export($c['Default'], true) . "\n";
}
