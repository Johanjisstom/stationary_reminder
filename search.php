
<?php

require_once __DIR__ . '/connect.php';

if (!isset($_SESSION['UserID'])) {
    echo '<p>Please log in to use the search feature.</p>';
    return;
}

$userID = (int) $_SESSION['UserID'];
$searchTerm = trim($_GET['q'] ?? '');
$results = [];
$errorMessage = '';

$tables = [
    'sticky_notes' => 'Sticky Notes',
    'to_dolist' => 'To-Do Lists',
    'stationerychecklist' => 'Stationery Checklist'
];

if ($searchTerm !== '') {
    $searchPattern = '%' . $searchTerm . '%';

    foreach ($tables as $table => $label) {
        try {
            // Check the actual columns in this table.
            $columnResult = $conn->query(
                "SHOW COLUMNS FROM `$table`"
            );

            $columns = [];
            $hasUserID = false;

            while ($column = $columnResult->fetch_assoc()) {
                $columnName = $column['Field'];
                $columnType = strtolower($column['Type']);

                if (strcasecmp($columnName, 'UserID') === 0) {
                    $hasUserID = true;
                }

                // Search text-based fields only.
                if (
                    preg_match(
                        '/^(varchar|char|text|tinytext|mediumtext|longtext)/',
                        $columnType
                    )
                    && !in_array(
                        strtolower($columnName),
                        ['password', 'email', 'note_colour'],
                        true
                    )
                ) {
                    $columns[] = $columnName;
                }
            }

            // Do not search records without user ownership.
            if (!$hasUserID || empty($columns)) {
                continue;
            }

            $conditions = [];

            foreach ($columns as $columnName) {
                $safeColumn = str_replace('`', '``', $columnName);
                $conditions[] = "`$safeColumn` LIKE ?";
            }

            $sql = "SELECT * FROM `$table`
                    WHERE UserID = ?
                    AND (" . implode(' OR ', $conditions) . ")
                    LIMIT 50";

            $stmt = $conn->prepare($sql);

            // One user ID and one search pattern per text column.
            $types = 'i' . str_repeat('s', count($columns));
            $values = [$userID];

            foreach ($columns as $columnName) {
                $values[] = $searchPattern;
            }

            $stmt->bind_param($types, ...$values);
            $stmt->execute();

            $queryResult = $stmt->get_result();

            while ($row = $queryResult->fetch_assoc()) {
                $results[] = [
                    'category' => $label,
                    'row' => $row
                ];
            }

            $stmt->close();

        } catch (Throwable $e) {
            // Continue searching the other tables if one table
            // has a different structure or cannot be searched.
            error_log('Search error in ' . $table . ': ' . $e->getMessage());
        }
    }
}
?>

<div class="search-page">
    <h2>Search</h2>
    <p>Find your saved sticky notes, tasks and stationery items.</p>

    <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="page" value="search">

        <input
            type="search"
            name="q"
            placeholder="Search tasks, notes or stationery..."
            value="<?= htmlspecialchars(
                $searchTerm,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <button type="submit">Search</button>
    </form>

    <?php if ($searchTerm !== ''): ?>
        <h3>
            Results for "<?= htmlspecialchars(
                $searchTerm,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        </h3>

        <?php if (empty($results)): ?>
            <p>No matching results found. Try another word.</p>
        <?php else: ?>
            <p><?= count($results) ?> result(s) found.</p>

            <div class="search-results">
                <?php foreach ($results as $item): ?>
                    <article class="search-result">
                        <h4>
                            <?= htmlspecialchars(
                                $item['category'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h4>

                        <?php
                        foreach ($item['row'] as $key => $value):
                            if (
                                $value === null ||
                                strcasecmp($key, 'UserID') === 0 ||
                                preg_match('/password|email/i', $key)
                            ) {
                                continue;
                            }
                        ?>
                            <p>
                                <strong>
                                    <?= htmlspecialchars(
                                        $key,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>:
                                </strong>

                                <?= htmlspecialchars(
                                    (string) $value,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                        <?php endforeach; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
