<?php
/**
 * Validation functions for NOARZ
 */

/**
 * Validate form data
 * @param array $data Form data
 * @param array $rules Validation rules
 * @return array Validation errors
 */
function validate_form($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $rule) {
        // Skip if field is not required and is empty
        if (isset($rule['required']) && !$rule['required'] && empty($data[$field])) {
            continue;
        }

        // Check if field exists
        if (!isset($data[$field])) {
            $errors[$field] = 'Field is required';
            continue;
        }

        $value = $data[$field];

        // Check if field is required
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = 'Field is required';
            continue;
        }

        // Check field type
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!validate_input($value, 'email')) {
                        $errors[$field] = 'Invalid email address';
                    }
                    break;

                case 'url':
                    if (!validate_input($value, 'url')) {
                        $errors[$field] = 'Invalid URL';
                    }
                    break;

                case 'username':
                    if (!validate_input($value, 'username')) {
                        $errors[$field] = 'Username must be 3-20 characters and contain only letters, numbers, and underscores';
                    }
                    break;

                case 'password':
                    if (!validate_input($value, 'password')) {
                        $errors[$field] = 'Password must be at least 6 characters';
                    }
                    break;

                case 'int':
                    if (!is_numeric($value) || intval($value) != $value) {
                        $errors[$field] = 'Must be an integer';
                    }
                    break;

                case 'float':
                    if (!is_numeric($value)) {
                        $errors[$field] = 'Must be a number';
                    }
                    break;
            }
        }

        // Check minimum length
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = 'Must be at least ' . $rule['min_length'] . ' characters';
        }

        // Check maximum length
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = 'Must be no more than ' . $rule['max_length'] . ' characters';
        }

        // Check minimum value
        if (isset($rule['min_value']) && $value < $rule['min_value']) {
            $errors[$field] = 'Must be at least ' . $rule['min_value'];
        }

        // Check maximum value
        if (isset($rule['max_value']) && $value > $rule['max_value']) {
            $errors[$field] = 'Must be no more than ' . $rule['max_value'];
        }

        // Check pattern
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            $errors[$field] = isset($rule['pattern_message']) ? $rule['pattern_message'] : 'Invalid format';
        }

        // Check custom validation
        if (isset($rule['custom']) && is_callable($rule['custom'])) {
            $custom_error = $rule['custom']($value, $data);
            if ($custom_error) {
                $errors[$field] = $custom_error;
            }
        }
    }

    return $errors;
}

/**
 * Validate file upload
 * @param array $file File data ($_FILES array element)
 * @param array $options Validation options
 * @return string|null Error message or null if valid
 */
function validate_file_upload($file, $options = []) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return isset($options['required']) && $options['required'] ? 'File is required' : null;
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            default:
                return 'File upload failed';
        }
    }

    // Check file size
    if (isset($options['max_size']) && $file['size'] > $options['max_size']) {
        return 'File is too large (maximum ' . format_file_size($options['max_size']) . ')';
    }

    // Check file type
    if (isset($options['allowed_types']) && !in_array($file['type'], $options['allowed_types'])) {
        return 'File type not allowed';
    }

    // Check file extension
    if (isset($options['allowed_extensions'])) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $options['allowed_extensions'])) {
            return 'File extension not allowed';
        }
    }

    return null;
}

/**
 * Format file size
 * @param int $size Size in bytes
 * @return string Formatted size
 */
function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;

    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }

    return round($size, 2) . ' ' . $units[$i];
}

/**
 * Validate CSRF token in form submission
 * @param array $data Form data
 * @return bool Valid or invalid
 */
function validate_form_csrf($data) {
    return isset($data['csrf_token']) && validate_csrf_token($data['csrf_token']);
}
?>
