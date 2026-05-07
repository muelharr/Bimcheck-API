<?php
// Database Helper Functions
// Simple abstraction untuk prepared statements

// Execute query dengan prepared statement
function db_query($conn, $query, $types = '', $params = []) {
    try {
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Bind parameters jika ada
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
        
    } catch (Exception $e) {
        error_log("DB Query Error: " . $e->getMessage());
        return false;
    }
}

// Fetch single row
function db_fetch($conn, $query, $types = '', $params = []) {
    $stmt = db_query($conn, $query, $types, $params);
    
    if (!$stmt) {
        return null;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

// Fetch all rows
function db_fetch_all($conn, $query, $types = '', $params = []) {
    $stmt = db_query($conn, $query, $types, $params);
    
    if (!$stmt) {
        return [];
    }
    
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $stmt->close();
    return $rows;
}

// Insert helper
function db_insert($conn, $table, $data) {
    $fields = array_keys($data);
    $values = array_values($data);
    
    $placeholders = str_repeat('?,', count($fields) - 1) . '?';
    $field_list = implode(',', $fields);
    
    $query = "INSERT INTO $table ($field_list) VALUES ($placeholders)";
    
    // Determine types
    $types = '';
    foreach ($values as $val) {
        if (is_int($val)) {
            $types .= 'i';
        } elseif (is_float($val)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }
    
    $stmt = db_query($conn, $query, $types, $values);
    
    if ($stmt) {
        $insert_id = $conn->insert_id;
        $stmt->close();
        return $insert_id;
    }
    
    return false;
}

// Update helper
function db_update($conn, $table, $data, $where_field, $where_value) {
    $set_parts = [];
    $values = [];
    
    foreach ($data as $field => $value) {
        $set_parts[] = "$field = ?";
        $values[] = $value;
    }
    
    $values[] = $where_value;
    $set_clause = implode(', ', $set_parts);
    
    $query = "UPDATE $table SET $set_clause WHERE $where_field = ?";
    
    // Determine types
    $types = '';
    foreach ($values as $val) {
        if (is_int($val)) {
            $types .= 'i';
        } elseif (is_float($val)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }
    
    $stmt = db_query($conn, $query, $types, $values);
    
    if ($stmt) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    return false;
}

// Delete helper
function db_delete($conn, $table, $where_field, $where_value) {
    $query = "DELETE FROM $table WHERE $where_field = ?";
    
    $type = is_int($where_value) ? 'i' : 's';
    $stmt = db_query($conn, $query, $type, [$where_value]);
    
    if ($stmt) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
    
    return false;
}

// Count rows
function db_count($conn, $table, $where_field = null, $where_value = null) {
    if ($where_field && $where_value) {
        $query = "SELECT COUNT(*) as total FROM $table WHERE $where_field = ?";
        $type = is_int($where_value) ? 'i' : 's';
        $result = db_fetch($conn, $query, $type, [$where_value]);
    } else {
        $query = "SELECT COUNT(*) as total FROM $table";
        $result = db_fetch($conn, $query);
    }
    
    return $result ? (int)$result['total'] : 0;
}
?>
