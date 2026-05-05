<?php
session_start();
include "../../../../dbconn.php";
require '../../../../asset/vendor/autoload.php';

$upload_dir = __DIR__ . '/../../../../asset/certificates/';
$adminid = $_SESSION['id'] ?? null;

if (!$adminid) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid user session']);
    exit();
}

// Make sure user is logged in and admin
if (!isset($_SESSION['fullname']) || $_SESSION['role'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}


//Handle delete request
if (isset($_GET['delete'])) {
    header('Content-Type: application/json');

    // Fetch the certificate file for the user
    $stmt = $conn->prepare("SELECT file_name FROM certificate WHERE adminid = ?");
    $stmt->bind_param("s", $adminid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $file = $upload_dir . $row['file_name'];

        if (file_exists($file)) {
            // Delete the file
            unlink($file);

            // Delete the DB record
            $stmt_del = $conn->prepare("DELETE FROM certificate WHERE adminid = ?");
            $stmt_del->bind_param("s", $adminid);
            $stmt_del->execute();

            echo json_encode(['status' => 'success', 'message' => 'Certificate deleted successfully.']);
        } else {
            // File missing on disk, but record exists
            echo json_encode(['status' => 'error', 'message' => 'Certificate record found but file is missing.']);
        }
    } else {
        // No DB record found
        echo json_encode(['status' => 'error', 'message' => 'No certificate found to delete.']);
    }
    exit;
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] == 1) {
    // If 'force' is set, serve the file directly (actual download)
    if (isset($_GET['force']) && $_GET['force'] == 1) {
        $stmt = $conn->prepare("SELECT file_name FROM certificate WHERE adminid = ?");
        $stmt->bind_param("s", $adminid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $fileName = $row['file_name'];
            $filePath = __DIR__ . '/../../../../asset/certificates/' . $fileName;

            if (file_exists($filePath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                flush();
                readfile($filePath);
                exit();
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'File not found on server.']);
                exit();
            }
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No certificate uploaded.']);
            exit();
        }
    }

    // Otherwise, just check if file exists and return JSON (used by AJAX to check)
    $stmt = $conn->prepare("SELECT file_name FROM certificate WHERE adminid = ?");
    $stmt->bind_param("s", $adminid);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: application/json');

    if ($row = $result->fetch_assoc()) {
        $fileName = $row['file_name'];
        $filePath = __DIR__ . '/../../../../asset/certificates/' . $fileName;

        if (file_exists($filePath)) {
            echo json_encode(['status' => 'success', 'file' => $fileName]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found on server.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No certificate uploaded.']);
    }
    exit();
}

// Handle file upload POST
if (!empty($_FILES['import_file']['name'])) {
    $fileName = $_FILES['import_file']['name'];
    $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed_ext = ['png', 'jpg', 'jpeg', 'pdf'];

    if (in_array($file_ext, $allowed_ext)) {
        if ($_FILES['import_file']['size'] <= 5 * 1024 * 1024) {

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Check if certificate exists, delete old file & DB record
            $stmt = $conn->prepare("SELECT file_name FROM certificate WHERE adminid = ?");
            $stmt->bind_param("s", $adminid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $oldFile = $upload_dir . $row['file_name'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
                $deleteStmt = $conn->prepare("DELETE FROM certificate WHERE adminid = ?");
                $deleteStmt->bind_param("s", $adminid);
                $deleteStmt->execute();
            }

            $newFileName = uniqid('cert_', true) . '.' . $file_ext;
            $destination = $upload_dir . $newFileName;

            if (move_uploaded_file($_FILES['import_file']['tmp_name'], $destination)) {
                $insertStmt = $conn->prepare("INSERT INTO certificate (adminid, file_name, upload_date) VALUES (?, ?, NOW())");
                $insertStmt->bind_param("ss", $adminid, $newFileName);
                $insertStmt->execute();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Certificate replaced successfully.',
                    'file' => $newFileName
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds limit.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
}



?>