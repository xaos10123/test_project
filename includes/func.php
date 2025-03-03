<?php

function updateCol($conn, string $name_col, string $old_value, string $new_value, $id):bool {
    if (checkCol($conn, $name_col, $new_value)){
        $query = "UPDATE users SET ".$name_col."= '$new_value' WHERE id = $id";
        $conn->query($query);
        return true;
    }
    return false;
}

function checkCol($conn, string $name_col, string $new_value):bool {
    try {
        $query = "SELECT * FROM users WHERE `$name_col` = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Ошибка подготовки запроса: " . $conn->error);
        }
        $stmt->bind_param("s", $new_value);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 0;

    } catch (Exception $e){
        echo $e->getMessage();
        return false;
    }
}

function isNew(string $old_value, string $new_value):bool {
    return $old_value !== $new_value;
}

function updatePass($conn, $new_password, $id){
    $query = "UPDATE users SET password = '$new_password' WHERE id = $id";
    $conn->query($query);
}

function checkPattern($pattern, $string, $err_mess){
    if (!preg_match($pattern, $string)) {
        return $err_mess;
    }
    else {
        return '';
    }
}
