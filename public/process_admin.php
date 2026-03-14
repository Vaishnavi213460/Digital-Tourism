<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { die("Unauthorized"); }

// HANDLE DELETES
if (isset($_GET['delete_id']) && isset($_GET['type'])) {
    $id = $_GET['delete_id'];
    $table = $_GET['type'];
    $allowed = ['users', 'destinations', 'packages', 'accommodations'];
    
    if (in_array($table, $allowed)) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin_dashboard.php?msg=Deleted Successfully");
        exit();
    }
}

// HANDLE ADDS & EDITS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $id = (int)$_POST['id'];

    if ($action == 'add_destination') {
        $image = $_FILES['image']['name'];
        $target = "../public/assets/img/" . basename($image);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO destinations (name, location, description, image_url, food_culture, attractions) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['location'], $_POST['description'], $image, $_POST['food_culture'], $_POST['attractions']]);
        }
        header("Location: admin_dashboard.php?msg=Added Successfully");
        exit();
    }

    if ($action == 'edit_destination') {
        $updateFields = [
            'name' => $_POST['name'],
            'location' => $_POST['location'],
            'description' => $_POST['description'],
            'food_culture' => $_POST['food_culture'],
            'attractions' => $_POST['attractions']
        ];
        $sql = "UPDATE destinations SET name=?, location=?, description=?, food_culture=?, attractions=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([ $updateFields['name'], $updateFields['location'], $updateFields['description'], 
                         $updateFields['food_culture'], $updateFields['attractions'], $id ]);
        
        // Handle optional image update
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $target = "../public/assets/img/" . basename($image);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $img_sql = "UPDATE destinations SET image_url=? WHERE id=?";
                $img_stmt = $pdo->prepare($img_sql);
                $img_stmt->execute([$image, $id]);
            }
        }
        header("Location: admin_dashboard.php?msg=Updated Successfully");
        exit();
    }

    if ($action == 'add_package') {
        $stmt = $pdo->prepare("INSERT INTO packages (destination_id, package_name, duration, price, inclusions) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['destination_id'], $_POST['package_name'], $_POST['duration'], $_POST['price'], $_POST['inclusions']]);
        header("Location: admin_dashboard.php?msg=Added Successfully");
        exit();
    }

    if ($action == 'edit_package') {
        $stmt = $pdo->prepare("UPDATE packages SET destination_id=?, package_name=?, duration=?, price=?, inclusions=? WHERE id=?");
        $stmt->execute([$_POST['destination_id'], $_POST['package_name'], $_POST['duration'], $_POST['price'], $_POST['inclusions'], $id]);
        header("Location: admin_dashboard.php?msg=Updated Successfully");
        exit();
    }

    if ($action == 'add_hotel') {
        $stmt = $pdo->prepare("INSERT INTO accommodations (destination_id, hotel_name, stars, transport_options) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['destination_id'], $_POST['hotel_name'], $_POST['stars'], $_POST['transport_options']]);
        header("Location: admin_dashboard.php?msg=Added Successfully");
        exit();
    }

    if ($action == 'edit_hotel') {
        $stmt = $pdo->prepare("UPDATE accommodations SET destination_id=?, hotel_name=?, stars=?, transport_options=? WHERE id=?");
        $stmt->execute([$_POST['destination_id'], $_POST['hotel_name'], $_POST['stars'], $_POST['transport_options'], $id]);
        header("Location: admin_dashboard.php?msg=Updated Successfully");
        exit();
    }
}
