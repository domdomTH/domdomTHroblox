<?php
require_once 'config.php';

/**
 * Connect to the database
 * @return mysqli Database connection
 */
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

/**
 * Ensure the news_images table exists
 * Creates the table if it doesn't exist
 * @return bool True if table exists or was created successfully
 */
function ensure_news_images_table() {
    static $table_checked = false;

    // Only check once per request for performance
    if ($table_checked) {
        return true;
    }

    $conn = db_connect();

    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'news_images'");
    $table_exists = $result && $result->num_rows > 0;

    // Create table if it doesn't exist
    if (!$table_exists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS news_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            news_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            position INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
        )";

        $table_exists = $conn->query($sql) === TRUE;
    }

    $conn->close();
    $table_checked = true;

    return $table_exists;
}

/**
 * Ensure the site_settings table exists
 * Creates the table if it doesn't exist and populates with default values
 * @return bool True if table exists or was created successfully
 */
function ensure_site_settings_table() {
    static $table_checked = false;

    // Only check once per request for performance
    if ($table_checked) {
        return true;
    }

    $conn = db_connect();

    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'site_settings'");
    $table_exists = $result && $result->num_rows > 0;

    // Create table if it doesn't exist
    if (!$table_exists) {
        $sql = "
        CREATE TABLE IF NOT EXISTS site_settings (
            setting_name VARCHAR(50) PRIMARY KEY,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $table_exists = $conn->query($sql) === TRUE;

        // Insert default settings
        if ($table_exists) {
            $default_settings = [
                ['youtube_url', 'https://youtube.com/channel/YOUR_CHANNEL_ID'],
                ['discord_url', 'https://discord.gg/YOUR_INVITE_CODE'],
                ['youtube_embed', 'LATEST_VIDEO_ID'],
                ['discord_widget_id', '1337499545852186806'],
                ['site_logo', 'images/logo.png']
            ];

            foreach ($default_settings as $setting) {
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?)");
                $stmt->bind_param("ss", $setting[0], $setting[1]);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $conn->close();
    $table_checked = true;

    return $table_exists;
}

/**
 * Get a setting value from the site_settings table
 * @param string $setting_name The name of the setting to retrieve
 * @param string $default Default value if setting doesn't exist
 * @return string The setting value
 */
function get_setting($setting_name, $default = '') {
    ensure_site_settings_table();

    try {
        $setting = db_get_row("SELECT setting_value FROM site_settings WHERE setting_name = ?", [$setting_name]);
        return $setting ? $setting['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update a setting in the site_settings table
 * @param string $setting_name The name of the setting to update
 * @param string $setting_value The new value
 * @return bool True if successful
 */
function update_setting($setting_name, $setting_value) {
    ensure_site_settings_table();

    try {
        // Check if setting exists
        $exists = db_get_row("SELECT 1 FROM site_settings WHERE setting_name = ?", [$setting_name]);

        if ($exists) {
            // Update existing setting
            return db_update('site_settings', ['setting_value' => $setting_value], 'setting_name = ?', [$setting_name]);
        } else {
            // Insert new setting
            return db_insert('site_settings', [
                'setting_name' => $setting_name,
                'setting_value' => $setting_value
            ]);
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Execute a query and return the result
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return mysqli_result|bool Query result
 */
function db_query($sql, $params = []) {
    $conn = db_connect();

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $types = '';
        $bindParams = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }

        $bindValues = array_merge([$types], $bindParams);
        $stmt->bind_param(...$bindValues);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}

/**
 * Get a single row from the database
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|null Row data
 */
function db_get_row($sql, $params = []) {
    $result = db_query($sql, $params);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get multiple rows from the database
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Rows data
 */
function db_get_rows($sql, $params = []) {
    $result = db_query($sql, $params);
    $rows = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    return $rows;
}

/**
 * Insert data into the database
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|bool Last insert ID or false on failure
 */
function db_insert($table, $data) {
    $conn = db_connect();

    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    $stmt = $conn->prepare($sql);

    $types = '';
    $bindParams = [];

    foreach ($data as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
        $bindParams[] = $value;
    }

    $bindValues = array_merge([$types], $bindParams);
    $stmt->bind_param(...$bindValues);

    $result = $stmt->execute();
    $insertId = $conn->insert_id;

    $stmt->close();
    $conn->close();

    return $result ? $insertId : false;
}

/**
 * Update data in the database
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $where WHERE clause
 * @param array $whereParams Parameters for WHERE clause
 * @return bool Success or failure
 */
function db_update($table, $data, $where, $whereParams = []) {
    $conn = db_connect();

    $setClauses = [];
    foreach (array_keys($data) as $column) {
        $setClauses[] = "$column = ?";
    }

    $setClause = implode(', ', $setClauses);

    $sql = "UPDATE $table SET $setClause WHERE $where";

    $stmt = $conn->prepare($sql);

    $types = '';
    $bindParams = array_merge(array_values($data), $whereParams);

    foreach ($bindParams as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
    }

    $bindValues = array_merge([$types], $bindParams);
    $stmt->bind_param(...$bindValues);

    $result = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $result;
}

/**
 * Delete data from the database
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool Success or failure
 */
function db_delete($table, $where, $params = []) {
    $conn = db_connect();

    $sql = "DELETE FROM $table WHERE $where";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $types = '';
        $bindParams = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }

        $bindValues = array_merge([$types], $bindParams);
        $stmt->bind_param(...$bindValues);
    }

    $result = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $result;
}
?>
